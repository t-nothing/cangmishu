<?php

namespace App\Models;

use App\Models\Model;

class UserExtra extends Model
{
    protected $table = 'user_extra';

    protected $fillable = [
        'user_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'share_count',
        'self_use_count',
        'rent_count',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * 已创建的公用仓库数
     * 
     * @return integer
     */
    public function getShareCountAttribute()
    {
        return Warehouse::where('type', Warehouse::TYPE_OPEN)
                        ->where('owner_id', $this->user_id)
                        ->count();
    }

    /**
     * 已创建的私用仓库数
     *
     * @return integer
     */
    public function getSelfUseCountAttribute()
    {
        return Warehouse::where('type', Warehouse::TYPE_PERSONAL)
                        ->where('owner_id', $this->user_id)
                        ->count();
    }

    /**
     * 已租用仓库数
     * 
     * @return integer
     */
    public function getRentCountAttribute()
    {
        return Warehouse::where('user_id', $this->user_id)
                        ->where('owner_id', '!=', $this->user_id)
                        ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Operations
    |--------------------------------------------------------------------------
    */

    public function isShareMax()
    {
        return $this->share_limit <= $this->share_count;
    }

    public function isSelfMax()
    {
        return $this->self_use_limit <= $this->self_use_count;
    }

    public function isRentMax()
    {
        return $this->rent_limit <= $this->rent_count;
    }

}