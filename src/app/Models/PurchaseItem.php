<?php

namespace App\Models;

class PurchaseItem extends Model
{

    const STATUS_PREPARE    = 1;// 待入库
    const STATUS_PROCEED    = 2;// 入库中
    const STATUS_ACCOMPLISH = 3;// 入库完成

    protected $table = 'purchase_item';

    public     $timestamps = true;

    protected $guarded  =[];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'status_name',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function getStatusNameAttribute()
    {
        return trans(sprintf("message.batchPurchaseItemStatus%s", $this->status));
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function logs()
    {
        return $this->hasMany('App\Models\PurchaseItemLog', 'purchase_item_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    /**
     * 限制查询属于指定仓库。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfWarehouse($query, $warehouse_id)
    {
        return $query->where('warehouse_id', $warehouse_id);
    }

    /**
     * 限制查询属于指定用户。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhose($query, $user_id)
    {
        return $query->where('owner_id', $user_id);
    }


}