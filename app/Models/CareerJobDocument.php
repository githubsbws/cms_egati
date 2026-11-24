<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A file of a recruitment notice: an uploaded PDF, or a link to a file on another website.
 */
class CareerJobDocument extends Model
{
    protected $fillable = ['job_id', 'labels', 'file_path', 'external_url', 'size_bytes', 'is_notice', 'sort_order'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['labels' => 'array', 'is_notice' => 'boolean'];
    }

    /**
     * @return BelongsTo<CareerJob, $this>
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(CareerJob::class, 'job_id');
    }

    /**
     * Label in a language, falling back to the default language, then to any label.
     */
    public function labelFor(string $locale, ?string $defaultLocale = null): string
    {
        $labels = array_filter((array) $this->labels, fn ($label) => filled($label));

        return $labels[$locale] ?? ($defaultLocale ? ($labels[$defaultLocale] ?? null) : null) ?? (reset($labels) ?: '');
    }

    public function getUrlAttribute(): ?string
    {
        return web_file_url($this->file_path) ?? $this->external_url;
    }

    public function getSizeMbAttribute(): ?string
    {
        return $this->size_bytes ? number_format($this->size_bytes / 1024 / 1024, 1) : null;
    }
}
