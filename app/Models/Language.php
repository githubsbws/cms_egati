<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'languages';

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'direction',
        'is_default',
        'is_active',
        'sort_order',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', 'y');
    }

    public function getIsActiveBoolAttribute(): bool
    {
        return $this->is_active === 'y';
    }

    public function getIsDefaultBoolAttribute(): bool
    {
        return $this->is_default === 'y';
    }
}
