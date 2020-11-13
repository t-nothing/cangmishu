<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as BaseUser;
use Tymon\JWTAuth\Contracts\JWTSubject;

class SuperAdmin extends BaseUser implements JWTSubject
{
    protected $table = 'super_admins';

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'forbid_login' => 'bool',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => 'super_admin',
        ];
    }
}
