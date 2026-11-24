<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerJobTranslation extends Model
{
    protected $fillable = ['job_id', 'locale', 'title', 'department', 'qualifications'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['qualifications' => 'array'];
    }
}
