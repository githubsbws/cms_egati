<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A published document: credit rating report, financial statement (Thai + English file) or annual report.
 */
class Publication extends Model
{
    use SoftDeletes;

    public const TYPES = ['credit', 'finance', 'annual'];

    /** Finance periods: quarters 1-3 and the full year (there is no Q4 statement). */
    public const PERIODS = ['1', '2', '3', 'annual'];

    protected $fillable = ['type', 'year', 'period', 'agency', 'rating', 'file_path', 'external_url', 'file_path_en', 'external_url_en', 'cover_image', 'is_published', 'updated_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'year' => 'integer'];
    }

    /**
     * @param  Builder<Publication>  $query
     * @return Builder<Publication>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * @param  Builder<Publication>  $query
     * @return Builder<Publication>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Buddhist era year (what the Thai pages show).
     */
    public function getBuddhistYearAttribute(): int
    {
        return $this->year + 543;
    }

    /**
     * Link to the document: an uploaded file, else the external link. $lang 'en' gives the English file of a financial statement.
     */
    public function documentUrl(string $lang = 'th'): ?string
    {
        if ($lang === 'en') {
            return web_file_url($this->file_path_en) ?? $this->external_url_en;
        }

        return web_file_url($this->file_path) ?? $this->external_url;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return web_file_url($this->cover_image);
    }
}
