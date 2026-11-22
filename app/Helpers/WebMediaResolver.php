<?php

namespace App\Helpers;

use App\Models\WebMedia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Resolves front office image/video slots. A slot key can be overridden from the
 * back office (web_media table). With no row, the default file shipped in
 * public/web-assets is used, so the page is never empty.
 */
class WebMediaResolver
{
    public const CACHE_KEY = 'web_media.overrides';

    /**
     * @var array<string, string>|null
     */
    protected static ?array $overrides = null;

    public static function url(string $key, string $default): string
    {
        $path = self::overrides()[$key] ?? null;

        return self::fileUrl($path) ?? asset('web-assets/'.ltrim($default, '/'));
    }

    /**
     * Paths under web-assets/ are shipped files; anything else is on the public disk.
     */
    public static function fileUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }
        if (str_starts_with($path, 'web-assets/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    public static function forget(): void
    {
        self::$overrides = null;
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, string>
     */
    protected static function overrides(): array
    {
        return self::$overrides ??= Cache::remember(
            self::CACHE_KEY,
            600,
            fn (): array => WebMedia::query()->pluck('path', 'key')->all(),
        );
    }
}
