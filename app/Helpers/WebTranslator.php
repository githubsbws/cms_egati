<?php

namespace App\Helpers;

use App\Models\Language;
use Illuminate\Support\Facades\Storage;

/**
 * Front office strings, kept separate from the back office (lang/th, lang/en).
 * These come from JSON files under storage/app/private/localization/web/, so a
 * new language can be added by dropping in a file, no code change needed.
 */
class WebTranslator
{
    protected static array $cache = [];

    public static function get(string $key, string $locale, ?string $fallback = null): string
    {
        $strings = self::strings($locale);

        if (array_key_exists($key, $strings)) {
            return $strings[$key];
        }

        $default = Language::active()->where('is_default', 'y')->value('code');

        if ($locale !== $default) {
            $defaultStrings = self::strings($default);

            if (array_key_exists($key, $defaultStrings)) {
                return $defaultStrings[$key];
            }
        }

        return $fallback ?? $key;
    }

    /**
     * @return array<string, string>
     */
    public static function strings(string $locale): array
    {
        if (isset(self::$cache[$locale])) {
            return self::$cache[$locale];
        }

        $path = rtrim(config('web_locale.storage_path'), '/')."/{$locale}.json";

        if (! Storage::disk('local')->exists($path)) {
            return self::$cache[$locale] = [];
        }

        $decoded = json_decode(Storage::disk('local')->get($path), true, 5, JSON_THROW_ON_ERROR);

        return self::$cache[$locale] = is_array($decoded['strings'] ?? null) ? $decoded['strings'] : [];
    }

    /**
     * Language metadata (name, direction) now comes from the languages table, not the JSON file.
     * The JSON file only holds the strings block.
     *
     * @return array<string, mixed>
     */
    public static function meta(string $locale): array
    {
        $language = Language::find($locale);

        if ($language === null) {
            return [];
        }

        return [
            'locale' => $language->code,
            'name' => $language->name,
            'native_name' => $language->native_name,
            'direction' => $language->direction,
        ];
    }
}
