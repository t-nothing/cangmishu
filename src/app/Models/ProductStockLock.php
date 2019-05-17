<?php

namespace App\Models;

class ProductStockLock extends Model
{
    protected $table = 'product_stock_lock';
    protected $fillable = ['relevance_code','stock_id','order_id','order_item_id','lock_amount','over_time'];
    public  $timestamps = true;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'over_time',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
        'over_time'  => 'date:Y-m-d H:i:s',
    ];

}