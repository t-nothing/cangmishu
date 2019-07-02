<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';

    const ACTIVATED   = 1;
    const UNACTIVATED = 0;

    const LOCKED   = 1;
    const UNLOCKED = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email','phone','nickname','remark'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
//        'name',
        // 'nickname',
        'password',
        'deleted_at',
        'latestAccessToken',
        'is_admin',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
//      'last_login_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
        'deleted_at' => 'date:Y-m-d H:i:s',
        'last_login_at' => 'date:Y-m-d H:i:s',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function extra()
    {
        return $this->hasOne('App\Models\UserExtra', 'user_id', 'id');
    }

    /**
     * Get all of the access tokens for the user.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function accessTokens()
    {
        return $this->hasMany('App\Models\Token', 'owner_user_id', 'id')
            ->valid()
            ->latest('expired_at');
    }

    /**
     * Get the latest access token for the user.
     *
     * @return \App\Models\Token|null
     */
    public function latestAccessToken()
    {
        return $this->hasOne('App\Models\Token', 'owner_user_id', 'id')
            ->valid()
            ->latest('expired_at');
    }

    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */

    public function groups()
    {
        return $this->belongsToMany('App\Models\Groups', 'user_group_rel', 'user_id','group_id');
    }

    public  function  warehouseEmployee()
    {
        return $this->hasOne('App\Models\WarehouseEmployee','user_id','id');
    }

    public  function  defaultWarehouse()
    {
        return $this->hasOne('App\Models\Warehouse','id','default_warehouse_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * 限制查询只包括指定关键字。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasKeyword($query, $keywords)
    {
        return $query->where('email', 'like', '%' . $keywords . '%');
    }

    /**
     * 限制仅当是管理员时。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyAdmin($query)
    {
        return $query->where('is_admin', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Operations
    |--------------------------------------------------------------------------
    */

    /**
     * 用户被锁定了吗？
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->is_locked == User::LOCKED;
    }

    /**
     * 用户通过邮箱激活了吗？
     *
     * @return bool
     */
    public function isActivated()
    {
        return $this->is_activated == User::ACTIVATED;
    }

    /**
     * 激活用户
     *
     * @return bool
     */
    public function setActivated()
    {
        $this->is_activated = User::ACTIVATED;

        return $this->save();
    }

    /**
     * 是否是管理员
     *
     * @return bool
     */
    public function isRoot()
    {
        return $this->id == 1;
    }

    /**
     * 是否是管理员
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->is_admin == 1;
    }

    /**
     * 设为管理员
     *
     * @return bool
     */
    public function setAdmin()
    {
        if (! $this->isActivated()) {
            throw new BusinessException('账户未激活');
        }

        $this->is_admin = 1;

        return $this->save();
    }

    /**
     * 取消管理员
     *
     * @return bool
     */
    public function cancelAdmin()
    {
        $this->is_admin = 0;

        return $this->save();
    }

    /**
     * Determine if the entity has a given ability.
     *
     * @param  integer  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function can($ability, $arguments = [])
    {
        $privileges = $this->rolePrivileges()->pluck('privilege_id');

        return in_array($ability, $privileges->toArray());
    }
}
