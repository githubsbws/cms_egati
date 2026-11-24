<?php

namespace App\Helpers;

use App\Models\HomeAgency;
use App\Models\HomeBlock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Home page content: a value saved in the back office wins, otherwise the default from config/home_content.php
 * (web strings in the visitor's language, files shipped in public/web-assets) is used. The page never ends up empty.
 */
class HomeContent
{
    /**
     * @var Collection<string, HomeBlock>|null
     */
    protected static ?Collection $blocks = null;

    public static function flush(): void
    {
        self::$blocks = null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function definition(string $code): array
    {
        foreach (config('home_content.sections') as $section) {
            if (isset($section['blocks'][$code])) {
                return $section['blocks'][$code];
            }
        }

        return [];
    }

    /**
     * Text saved in the back office for a language, as plain text (older saves may still carry markup).
     */
    public static function saved(string $code, string $field, string $locale): string
    {
        return self::toPlain((string) (self::block($code)?->texts->firstWhere('locale', $locale)?->fields[$field] ?? ''));
    }

    /**
     * Markup removed, <br> turned into a new line.
     */
    public static function toPlain(string $text): string
    {
        $text = preg_replace('#<br\s*/?>#i', "\n", $text) ?? '';

        return trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5));
    }

    /**
     * Translated text of a block: the saved text for the language, else the default.
     */
    public static function text(string $code, string $field, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $saved = self::saved($code, $field, $locale);

        return $saved !== '' ? $saved : self::defaultText($code, $field, $locale);
    }

    /**
     * Same as text() for places that print HTML: a saved text is plain, so it is escaped and its new lines become <br>;
     * the default text of the website may carry its own markup.
     */
    public static function html(string $code, string $field, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $saved = self::saved($code, $field, $locale);

        return $saved !== '' ? nl2br(e($saved)) : self::defaultText($code, $field, $locale);
    }

    public static function defaultText(string $code, string $field, string $locale): string
    {
        $definition = self::definition($code)['texts'][$field] ?? [];
        if (isset($definition['key'])) {
            return WebTranslator::get($definition['key'], $locale);
        }

        return (string) ($definition['text'] ?? '');
    }

    /**
     * Default text as plain text for the back office form (line breaks kept, markup removed).
     */
    public static function defaultPlain(string $code, string $field, string $locale): string
    {
        return self::toPlain(self::defaultText($code, $field, $locale));
    }

    /**
     * Saved value of a non-translated field (url, duration), else its default.
     */
    public static function plain(string $code, string $field): string
    {
        $saved = trim((string) (self::block($code)?->plain[$field] ?? ''));

        return $saved !== '' ? $saved : (string) (self::definition($code)['plain'][$field]['default'] ?? '');
    }

    /**
     * A link field as a full URL: http(s) as typed, anything else is a path inside the site.
     */
    public static function url(string $code, string $field): string
    {
        $value = self::plain($code, $field);

        return preg_match('#^https?://#i', $value) ? $value : web_url(ltrim($value, '/'));
    }

    public static function video(string $code): string
    {
        return self::media($code, 'video');
    }

    public static function poster(string $code): string
    {
        return self::media($code, 'poster');
    }

    public static function hasSavedMedia(string $code, string $kind): bool
    {
        return self::savedPath($code, $kind) !== null;
    }

    /**
     * Active agencies in display order, with their names loaded.
     *
     * @return Collection<int, HomeAgency>
     */
    public static function agencies(): Collection
    {
        try {
            return HomeAgency::query()->where('is_active', true)->ordered()->with('translations')->get();
        } catch (Throwable) {
            return collect();
        }
    }

    protected static function media(string $code, string $kind): string
    {
        $path = self::savedPath($code, $kind);
        if ($path !== null) {
            return (string) web_file_url($path);
        }

        return asset('web-assets/'.ltrim((string) (self::definition($code)[$kind] ?? ''), '/'));
    }

    /**
     * The saved file, only when it still exists on disk.
     */
    protected static function savedPath(string $code, string $kind): ?string
    {
        $path = self::block($code)?->{$kind.'_path'};
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return $path;
    }

    protected static function block(string $code): ?HomeBlock
    {
        if (self::$blocks === null) {
            try {
                self::$blocks = HomeBlock::query()->with('texts')->get()->keyBy('code');
            } catch (Throwable) {
                self::$blocks = collect();
            }
        }

        return self::$blocks->get($code);
    }
}
