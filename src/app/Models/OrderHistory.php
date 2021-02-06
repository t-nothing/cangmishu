<?php

namespace App\Models;

class OrderHistory extends Model
{
    protected $table = 'order_history';

    protected $guarded = [];
    protected $appends = [
        'status_name'
    ];
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
        $name = '';

        switch ($this->status) {
            case Order::STATUS_CANCEL:
                $name = "message.orderStatusCancel";
                break;
            case Order::STATUS_DEFAULT:
                $name = "message.orderStatusUnConfirm";
                break;
            case Order::STATUS_PICKING:
               $name = "message.orderStatusPicking";
               break;
            case Order::STATUS_PICK_DONE:
                $name = "message.orderStatusOutbound";
                break;
            case Order::STATUS_WAITING:
               $name = "message.orderStatusUnSend";
               break;
            case Order::STATUS_SENDING:
               $name = "message.orderStatusSending";
               break;
            case Order::STATUS_SUCCESS:
               $name = "message.orderStatusSuccess";
               break;
            default:
                break;
        }

        return trans($name);
    }
}
