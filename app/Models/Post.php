<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'cover_image',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('published_at', '<=', now());
    }

    /**
     * Only posts that have a translation in the given language (no translation, no page).
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeTranslatedIn(Builder $query, string $locale): Builder
    {
        return $query->whereHas('translations', fn (Builder $q) => $q->where('locale', $locale));
    }

    /**
     * @return HasMany<PostTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function translation(string $locale): ?PostTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * Cover image URL, or the default news cover when none was uploaded.
     */
    public function getCoverUrlAttribute(): string
    {
        return web_file_url($this->cover_image) ?? web_media('news.default_cover', 'presentation-poster.png');
    }
}
