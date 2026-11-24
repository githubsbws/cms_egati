<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrgMember extends Model
{
    use SoftDeletes;

    protected $fillable = ['group_id', 'photo_path', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @return BelongsTo<OrgGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(OrgGroup::class, 'group_id');
    }

    /**
     * @return HasMany<OrgMemberTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(OrgMemberTranslation::class, 'member_id');
    }

    public function translation(string $locale): ?OrgMemberTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * The photo URL, or null (the website shows a plain placeholder then).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return web_file_url($this->photo_path);
    }
}
