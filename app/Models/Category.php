<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'accent_color', 'sort_order'];

    /**
     * @return HasMany<CategoryTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function nameFor(string $locale): string
    {
        $translations = $this->translations;

        return $translations->firstWhere('locale', $locale)?->name
            ?? $translations->first()?->name
            ?? $this->code;
    }
}
