<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Lang;

/**
 * Looks up a back office menu label by its config key in lang/{locale}/admin/menuleft.php.
 * The key can sit at the top level or inside any nested group (set_sub, content_sub, ...).
 */
class MenuLabel
{
    public static function for(string $key, ?string $fallback = null): string
    {
        $lines = Lang::get('admin/menuleft');

        $found = is_array($lines) ? self::find($lines, $key) : null;

        return $found ?? $fallback ?? $key;
    }

    /**
     * @param  array<string, mixed>  $lines
     */
    protected static function find(array $lines, string $key): ?string
    {
        if (isset($lines[$key]) && is_string($lines[$key])) {
            return $lines[$key];
        }

        foreach ($lines as $value) {
            if (is_array($value) && ($hit = self::find($value, $key)) !== null) {
                return $hit;
            }
        }

        return null;
    }
}
