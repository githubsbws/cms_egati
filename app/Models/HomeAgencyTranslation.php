<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeAgencyTranslation extends Model
{
    protected $fillable = ['agency_id', 'locale', 'name'];
}
