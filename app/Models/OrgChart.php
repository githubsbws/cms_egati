<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgChart extends Model
{
    protected $primaryKey = 'locale';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['locale', 'image_path'];

    /**
     * The chart for a language, else the default language's chart (null if none uploaded at all).
     */
    public static function imageFor(string $locale): ?string
    {
        $default = Language::where('is_default', 'y')->value('code');
        $path = static::whereIn('locale', array_unique([$locale, (string) $default]))->pluck('image_path', 'locale');

        return web_file_url($path[$locale] ?? $path[$default] ?? null);
    }
}
