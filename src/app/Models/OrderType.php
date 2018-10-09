<?php

namespace App\Models;

use App\Models\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class OrderType extends Model
{
    use SoftDeletes;

    protected $table = 'order_type';

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
}
