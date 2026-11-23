<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrgGroup extends Model
{
    protected $fillable = ['code', 'type', 'document_path', 'sort_order', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * Groups shown on a back office page: "board" = the board and its committees, "management" = management team.
     *
     * @param  Builder<OrgGroup>  $query
     * @return Builder<OrgGroup>
     */
    public function scopeForSection(Builder $query, string $section): Builder
    {
        return $query->whereIn('type', $section === 'management' ? ['management'] : ['board', 'committee']);
    }

    /**
     * @return HasMany<OrgGroupTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(OrgGroupTranslation::class, 'group_id');
    }

    /**
     * @return HasMany<OrgMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(OrgMember::class, 'group_id')->orderBy('id');
    }

    /**
     * Name in a language, else in the fallback language (the default one), else any, else the code.
     */
    public function nameFor(string $locale, ?string $fallback = null): string
    {
        return $this->translations->firstWhere('locale', $locale)?->name
            ?? ($fallback ? $this->translations->firstWhere('locale', $fallback)?->name : null)
            ?? $this->translations->first()?->name
            ?? $this->code;
    }
}
