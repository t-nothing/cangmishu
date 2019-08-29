<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User;
use App\Models\Warehouse;
use Hash;

class AppAccount extends User
{

    protected $table = 'app_account';


    public function warehouse()
    {
        return $this->hasOne('App\Models\Warehouse', 'id', 'warehouse_id');
    }

    public function getAuthIdentifierName()
    {
        return 'warehouse_id';
    }


    public function user()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }


    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {

    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {

    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {

    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {

    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {

    }


    /**
     * 生成APP 密钥
     **/
    public static function generateAppSecret($warehouse_id, $key)
    {
        return Hash::make(sprintf("%s%s%s", $key, rand(100, 999), $warehouse_id));
    }
}