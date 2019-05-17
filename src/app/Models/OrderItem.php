<?php

namespace App\Models;

use App\Models\Model;
use App\Models\ProductStock;

class OrderItem extends Model
{
    protected $table = 'order_item';

    protected $guarded = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'send_date',
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
        'send_date' => 'date:Y-m-d H:i:s',
    ];

//    protected $appends = [
//        'product_name',
//    ];

    // public function productStock()
    // {
    //     return $this->hasMany('App\Models\ProductStock', 'relevance_code', 'relevance_code')
    //         ->where('status', ProductStock::GOODS_STATUS_ONLINE)
    //         ->orderBy('expiration_date', 'asc')
    //         ->with('tray.shelf');
    // }

    // public function orderproductStock()
    // {
    //     return $this->belongsTo('App\Models\ProductStock', 'relevance_code', 'relevance_code')
    //         ->where('status', ProductStock::GOODS_STATUS_ONLINE)
    //         ->where('stockin_num','>','0')
    //         ->orderBy('expiration_date', 'asc')
    //         ->with('tray.shelf.warehouseArea');
    // }

    public function order()
    {
        return $this->belongsTo('App\Models\Order', 'order_id', 'id');
    }

    public function pick()
    {
        return $this->belongsTo('App\Models\Pick', 'shipment_num', 'shipment_num');
    }

    public function stock()
    {
        return $this->belongsTo('App\Models\ProductStock', 'product_stock_id', 'id');
    }

    public function spec()
    {
        return $this->belongsTo('App\Models\ProductSpec', 'relevance_code', 'relevance_code')
            ->where('warehouse_id', $this->warehouse_id)
            ->where('owner_id', $this->owner_id);
    }

    public function feature()
    {
        return $this->belongsTo('App\Models\WarehouseFeature', 'warehouse_feature_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function getProductNameAttribute()
    {
        $name = '';
        $lang = app('translator')->locale()?:'cn';
        if($lang == 'zh-CN'){
            $lang = 'cn';
        }
        if (isset($this->spec, $this->spec->product)) {
            $name = $this->spec->product['name_'.$lang].
            '('.
            $this->spec['name_'.$lang].
            ')';
        }

        return $name;
    }

    /*
    |--------------------------------------------------------------------------
    | Operations
    |--------------------------------------------------------------------------
    */

    /**
     * 拣货单获取订单详情
     * @param $shipment_num
     * @return bool
     */
    public function shipmentGetInfo($shipment_num)
    {
        $data = $this->where('shipment_num', $shipment_num)->get();
        return $data ? $data->toArray() : false;
    }

    public function getExpressOne($express_num){
        $data=$this->where('express_num',$express_num)->first();
        return $data?$data->toArray():false;
    }

    public function  ScopeOfWarehouse($query,$warehouse_id)
    {
        return $query->where('warehouse_id',$warehouse_id);
    }

    public function  ScopeWhose($query,$owner_id)
    {
        return $query->where('owner_id',$owner_id);
    }
}