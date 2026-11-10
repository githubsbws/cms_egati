<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Users extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'remember_token', 'created_at', 'updated_at', '_token', 'group_id', 'superuser', 'del_status', 'last_activity', 'lastvisit_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        '_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'lastvisit_at' => 'datetime',
            'last_activity' => 'datetime',
        ];
    }

    public function profile()
    {
        return $this->hasOne(Profiles::class, 'user_id', 'id');
    }
}
