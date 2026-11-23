<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Related agency logo shown at the bottom of the home page.
 */
class HomeAgency extends Model
{
    use SoftDeletes;

    protected $fillable = ['logo_path', 'url', 'sort_order', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @param  Builder<HomeAgency>  $query
     * @return Builder<HomeAgency>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<HomeAgencyTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(HomeAgencyTranslation::class, 'agency_id');
    }

    public function translation(string $locale): ?HomeAgencyTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * Name in a language, falling back to the default language, then to any name.
     */
    public function nameFor(string $locale, ?string $defaultLocale = null): string
    {
        return $this->translation($locale)?->name
            ?? ($defaultLocale ? $this->translation($defaultLocale)?->name : null)
            ?? $this->translations->first()?->name
            ?? '';
    }

    public function getLogoUrlAttribute(): ?string
    {
        return web_file_url($this->logo_path);
    }
}
