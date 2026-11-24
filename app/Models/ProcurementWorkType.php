<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcurementWorkType extends Model
{
    protected $fillable = ['name', 'name_en', 'sort_order', 'is_active'];

    /**
     * Choices that can be picked for new records.
     *
     * @param  Builder<ProcurementWorkType>  $query
     * @return Builder<ProcurementWorkType>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', 'y');
    }

    /**
     * @return HasMany<ProcurementRecord, $this>
     */
    public function records(): HasMany
    {
        return $this->hasMany(ProcurementRecord::class, 'work_type_id');
    }

    /**
     * Name for a website language: the English name on English pages when there is one, else the Thai name.
     */
    public function labelFor(string $locale): string
    {
        return $locale === 'en' && filled($this->name_en) ? $this->name_en : $this->name;
    }
}
