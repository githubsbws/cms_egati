<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One event photo of a news post.
 */
class PostImage extends Model
{
    protected $fillable = ['post_id', 'path', 'sort_order'];

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function getUrlAttribute(): ?string
    {
        return web_file_url($this->path);
    }
}
