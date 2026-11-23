<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeBlockText extends Model
{
    protected $fillable = ['block_code', 'locale', 'fields'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['fields' => 'array'];
    }
}
