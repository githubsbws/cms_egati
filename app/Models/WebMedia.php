<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebMedia extends Model
{
    protected $table = 'web_media';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'path', 'alt'];
}
