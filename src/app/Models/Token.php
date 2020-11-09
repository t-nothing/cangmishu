<?php

namespace App\Models;

use App\Models\Model;
use Carbon\Carbon;

class Token extends Model
{
    protected $table = 'token';

    const TYPE_ACCESS_TOKEN    = 1;// access token
    const TYPE_EMAIL_CONFIRM   = 2;// email confirm
    const TYPE_FORGET_PASSWORD = 3;// forget password
    const TYPE_ACTIVATE        = 4;// activate

    const VALID   = 1;
    const INVALID = 0;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'expired_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
        'expired_at' => 'date:Y-m-d H:i:s',
    ];

    /**
     * 获取 token 所属的用户。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'owner_user_id', 'id');
    }

    /**
     * 获取有效 token。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', '=', Token::VALID)
                     ->where('expired_at', '>', time());
    }

    public function isValid()
    {
        return $this->is_valid == Token::VALID;
    }

    public function setInvalid()
    {
    	$this->is_valid = Token::INVALID;

    	return $this->save();
    }

    /**
     * Get a token by the given user ID and token ID.
     *
     * @param  string  $id
     * @param  int  $userId
     * @return \Laravel\Passport\Token|null
     */
    public function findForUser($id, $userId)
    {
        return Token::where('id', $id)->where('owner_user_id', $userId)->first();
    }

    /**
     * Get the token instances for the given user ID.
     *
     * @param  mixed  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUser($userId)
    {
        return Token::where('owner_user_id', $userId)->get();
    }

    /**
     * Get a valid token instance for the given user and client.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @param  \Laravel\Passport\Client  $client
     * @return \Laravel\Passport\Token|null
     */
    public function getValidToken($user)
    {
        return $this->where($user->getKeyName(), $user->getKey())
                    ->valid()
                    ->first();
    }
}
