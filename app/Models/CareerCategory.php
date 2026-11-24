<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Job family of a recruitment notice.
 */
class CareerCategory extends Model
{
    protected $fillable = ['name', 'name_en', 'sort_order', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @param  Builder<CareerCategory>  $query
     * @return Builder<CareerCategory>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @return HasMany<CareerJob, $this>
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(CareerJob::class, 'category_id');
    }

    /**
     * The English name on English pages when there is one, else the Thai name.
     */
    public function labelFor(string $locale): string
    {
        return $locale === 'th' || blank($this->name_en) ? $this->name : $this->name_en;
    }
}
