<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminGroup extends Model
{
    protected $table = 'admin_group';

    protected $fillable = [
        'group_name',
        'active',
        'is_superuser',
    ];

    protected $casts = [
        'active' => 'string',
    ];
}
