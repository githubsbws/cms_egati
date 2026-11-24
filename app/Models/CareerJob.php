<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One recruitment notice (a position with its dates and documents).
 */
class CareerJob extends Model
{
    use SoftDeletes;

    public const STAGES = ['application', 'assessment', 'results'];

    /** Order used on the website: open first, then opening soon, then the later stages. */
    public const STATUS_ORDER = ['open' => 0, 'upcoming' => 1, 'assessment' => 2, 'results' => 3, 'closed' => 4];

    protected $fillable = ['slug', 'category_id', 'vacancies', 'start_on', 'deadline_on', 'updated_on', 'stage', 'is_published', 'updated_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['start_on' => 'date', 'deadline_on' => 'date', 'updated_on' => 'date', 'is_published' => 'boolean'];
    }

    /**
     * @param  Builder<CareerJob>  $query
     * @return Builder<CareerJob>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * @return BelongsTo<CareerCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CareerCategory::class, 'category_id');
    }

    /**
     * @return HasMany<CareerJobTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CareerJobTranslation::class, 'job_id');
    }

    /**
     * @return HasMany<CareerJobDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(CareerJobDocument::class, 'job_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Needs the translations relation loaded.
     */
    public function translation(string $locale): ?CareerJobTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * The translation for a language, falling back to the default language, then to any.
     */
    public function translationFor(string $locale, ?string $defaultLocale = null): ?CareerJobTranslation
    {
        return $this->translation($locale)
            ?? ($defaultLocale ? $this->translation($defaultLocale) : null)
            ?? $this->translations->first();
    }

    /**
     * open | upcoming | closed while applications are the stage; otherwise the stage itself (assessment | results).
     * Applications open at 08:00 on the start day and close at 17:00 on the deadline day, Thailand time.
     */
    public function status(?CarbonInterface $now = null): string
    {
        if ($this->stage !== 'application') {
            return $this->stage;
        }
        $now ??= now('Asia/Bangkok');
        $start = Carbon::parse($this->start_on->format('Y-m-d').' 08:00:00', 'Asia/Bangkok');
        $end = Carbon::parse($this->deadline_on->format('Y-m-d').' 17:00:00', 'Asia/Bangkok');

        return match (true) {
            $now->lt($start) => 'upcoming',
            $now->lte($end) => 'open',
            default => 'closed',
        };
    }

    public function noticeDocument(): ?CareerJobDocument
    {
        return $this->documents->firstWhere('is_notice', true) ?? $this->documents->first();
    }
}
