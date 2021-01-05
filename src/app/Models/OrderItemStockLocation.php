<?php

namespace App\Models;


class OrderItemStockLocation extends Model
{
    
    protected $table = 'order_item_stock_location';

    public     $timestamps = true;

    protected $guarded  =[];

    protected $moveQty = 0;

    protected $fillable = ['stock_id', 'warehouse_location_id', 'warehouse_location_code', 'warehouse_id', 'product_stock_location_id', 'item_id', 'pick_num', 'shipment_num','stock_sku', 'relevance_code', 'verify_num'];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'location_code',
    ];


    public function setMoveQty(int $v)
    {
        $this->moveQty = $v;
        return $this;
    }


    public function getMoveQty()
    {
        return $this->moveQty;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function stock()
    {
        return $this->belongsTo('App\Models\ProductStock', 'stock_id', 'id');
    }

    public function spec()
    {
        return $this->belongsTo('App\Models\ProductSpec', 'spec_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo('App\Models\WarehouseLocation', 'warehouse_location_id', 'id');
    }

    public function pick()
    {
        return $this->belongsTo('App\Models\Pick', 'shipment_num', 'shipment_num');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\OrderItem', 'id', 'item_id');
    }

    /*
    |--------------------------------------------------------------------------
    | 属性
    |--------------------------------------------------------------------------
    */
    public  function  getSpecProductAttribute()
    {

        return [
            "id"                =>$this->spec->id,
            "produc_id"         =>$this->spec->product->id,
            "name_cn"           => sprintf("%s-%s", $this->spec->product->name_cn, $this->spec->name_cn),
            "name_en"           => sprintf("%s-%s", $this->spec->product->name_en, $this->spec->name_en),
            "photos"            =>$this->spec->product->photos
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 位置代码
    |--------------------------------------------------------------------------
    */
    public  function  getLocationCodeAttribute()
    {

        return WarehouseLocation::getCode($this->warehouse_id);
    }



}