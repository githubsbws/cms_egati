<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permission';

    protected $fillable = [
        'admin_menu_id',
        'group_id',
        'active',
    ];

    protected $casts = [
        'active' => 'string',
    ];
}
