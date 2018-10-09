<?php

namespace App\Models;

use App\Models\Model;
use App\Models\Order;

class OrderHistory extends Model
{
    protected $table = 'order_history';

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function order()
    {
    	return $this->belongsTo('App\Models\Order', 'order_id', 'id');
    }

    public static function addHistory(Order $order, $status)
    {
        return $history = OrderHistory::firstOrCreate(['order_id' => $order->id, 'status' => $status], [
            'owner_id' => $order->owner_id,
            'warehouse_id' => $order->warehouse_id,
        ]);
    }
}
