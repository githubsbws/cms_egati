<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Admin overrides of one home block (see config/home_content.php for the blocks and their default content).
 */
class HomeBlock extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['code', 'video_path', 'poster_path', 'plain', 'updated_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['plain' => 'array'];
    }

    /**
     * @return HasMany<HomeBlockText, $this>
     */
    public function texts(): HasMany
    {
        return $this->hasMany(HomeBlockText::class, 'block_code', 'code');
    }
}
