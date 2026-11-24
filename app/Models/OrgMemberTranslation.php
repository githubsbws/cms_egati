<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgMemberTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['member_id', 'locale', 'name', 'role'];
}
