<?php

namespace App\Models;

class OrderType extends Model
{
    protected $table = 'order_type';

    protected $hidden = [
        'deleted_at',
    ];

    protected  $fillable= ['name','is_enabled','owner_id'];
    protected  $guarded = [];
    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function warehouseArea()
    {
        return $this->belongsTo('App\Models\WarehouseArea', 'area_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order', 'order_type', 'id');
    }

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

    public  function ScopeOfWhose($query,$owner_id)
    {
        return $query->where('owner_id',$owner_id);
    }

    public function scopeOfWarehouse($query, $warehouse_id)
    {
        return $query->where('warehouse_id', $warehouse_id);
    }

}
