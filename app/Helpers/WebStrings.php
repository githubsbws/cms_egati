<?php

namespace App\Helpers;

use App\Models\Language;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use JsonException;

/**
 * Reads, checks and writes the front office string files (one JSON per language).
 * File: <localization/web>/{code}.json with { "meta": {...}, "strings": { "key": "text" } }.
 * The website only reads "strings"; "meta" is kept for humans. Language details come from the languages table.
 */
class WebStrings
{
    /** Tags allowed inside a string (no attributes). Anything else is rejected. */
    private const ALLOWED_TAGS = ['br', 'span', 'em', 'strong', 'b', 'i', 'small'];

    private const MAX_KEYS = 5000;

    private const MAX_LENGTH = 5000;

    public static function path(string $code): string
    {
        return rtrim(config('web_locale.storage_path'), '/')."/{$code}.json";
    }

    public static function exists(string $code): bool
    {
        return Storage::disk('local')->exists(self::path($code));
    }

    /**
     * @return array<string, string>
     */
    public static function read(string $code): array
    {
        if (! self::exists($code)) {
            return [];
        }
        $data = json_decode(Storage::disk('local')->get(self::path($code)), true);

        return is_array($data['strings'] ?? null) ? $data['strings'] : [];
    }

    /**
     * The file as text for download or editing: meta + strings, pretty printed.
     */
    public static function fileContents(Language $language): string
    {
        return self::encode($language, self::read($language->code));
    }

    /**
     * Save the strings of a language (checked first). The file is written to a temp file and
     * renamed, so a failed save never leaves a half-written language file.
     *
     * @param  array<string, string>  $strings
     */
    public static function write(Language $language, array $strings): void
    {
        $disk = Storage::disk('local');
        $path = self::path($language->code);
        $disk->makeDirectory(dirname($path));
        $temp = $path.'.tmp';
        $disk->put($temp, self::encode($language, $strings));
        $disk->move($temp, $path);
    }

    /**
     * Turn uploaded/edited JSON text into a clean strings array, or throw a validation error on "json".
     * Accepts the whole file ({meta, strings}) or only the strings object.
     *
     * @return array<string, string>
     */
    public static function parse(string $json): array
    {
        try {
            $data = json_decode(trim($json), true, 8, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            self::fail(__('admin/language.json_invalid', ['error' => $e->getMessage()]));
        }
        if (is_array($data) && is_array($data['strings'] ?? null)) {
            $data = $data['strings'];
        }
        if (! is_array($data) || $data === [] || array_is_list($data)) {
            self::fail(__('admin/language.json_shape'));
        }
        if (count($data) > self::MAX_KEYS) {
            self::fail(__('admin/language.json_too_many'));
        }

        $strings = [];
        foreach ($data as $key => $value) {
            $key = (string) $key;
            if (! preg_match('/^[A-Za-z0-9_.-]{1,200}$/', $key)) {
                self::fail(__('admin/language.json_bad_key', ['key' => $key]));
            }
            if (! is_string($value) || mb_strlen($value) > self::MAX_LENGTH) {
                self::fail(__('admin/language.json_bad_value', ['key' => $key]));
            }
            if (self::hasForbiddenMarkup($value)) {
                self::fail(__('admin/language.json_bad_markup', ['key' => $key]));
            }
            $strings[$key] = $value;
        }

        return $strings;
    }

    /**
     * Strings are printed as HTML in some places, so only plain line/emphasis tags are accepted.
     */
    private static function hasForbiddenMarkup(string $value): bool
    {
        if (preg_match('/javascript:|on\w+\s*=/i', $value) && str_contains($value, '<')) {
            return true;
        }
        preg_match_all('/<[^>]*>?/', $value, $tags);
        foreach ($tags[0] as $tag) {
            if (! preg_match('#^</?('.implode('|', self::ALLOWED_TAGS).')\s*/?>$#i', $tag)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, string>  $strings
     */
    private static function encode(Language $language, array $strings): string
    {
        return json_encode([
            'meta' => [
                'schema_version' => 1,
                'locale' => $language->code,
                'name' => $language->name,
                'native_name' => $language->native_name,
                'direction' => $language->direction,
            ],
            'strings' => $strings,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private static function fail(string $message): never
    {
        throw ValidationException::withMessages(['json' => $message]);
    }
}
