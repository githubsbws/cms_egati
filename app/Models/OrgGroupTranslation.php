<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgGroupTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['group_id', 'locale', 'name'];
}
