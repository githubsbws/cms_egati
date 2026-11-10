<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profiles extends Model
{
    protected $table = 'profiles';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'title_id',
        'firstname',
        'lastname', 'firstname_en', 'lastname_en', 'phone',
    ];

    public function fullname ()
    {
        return $this->firstname.' '.$this->lastname;
    }

    public function users()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }
}
