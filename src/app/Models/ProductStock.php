<?php

namespace App\Models;

use App\Exceptions\BusinessException;

class ProductStock extends Model
{
    const GOODS_STATUS_PREPARE = 1; // 待入库的
    const GOODS_STATUS_ONLINE  = 2; // 正常商品，可以售卖
    const GOODS_STATUS_OFFLINE = 3; // 在仓库，不卖了，下架了

    protected $table = 'product_stock';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
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
        'expiration_date' => 'date:Y-m-d',
        'best_before_date' => 'date:Y-m-d',
    ];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'relevance_code_barcode',
    ];

    protected  $fillable =['owner_id','spec_id','relevance_code','need_num','remark','distributor_id','distributor_code','warehouse_id','status','sku','ean'];
    protected $guarded = [];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse','warehouse_id', 'id');
    }

    public function batch()
    {
        return $this->belongsTo('App\Models\Batch', 'batch_id', 'id');
    }

    public function spec()
    {
        return $this->belongsTo('App\Models\ProductSpec', 'spec_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany('App\Models\ProductStockLog', 'product_stock_id', 'id');
    }

    public function locations()
    {
        return $this->belongsTo('App\Models\ProductStockLocation', 'id', 'stock_id');
    }

    /**
     * @deprecated
     */
    public function log()
    {
        return $this->hasMany('App\Models\ProductStockLog', 'sku', 'sku');
    }



    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfWarehouse($query,$warehosue_id)
    {
        return $query->where('warehouse_id', $warehosue_id);
    }



    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', ProductStock::GOODS_STATUS_ONLINE);
    }

    /**
     * 限制查询只包括指定关键字。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasKeyword($query, $keywords)
    {
        return $query->where('relevance_code', 'like', '%' . $keywords . '%');
    }

    public function scopeSku($query, $sku)
    {
        return $query->where('sku', $sku );
    }

    public function scopeWhose($query, $user_id)
    {
        return $query->where('owner_id', $user_id );
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public  function getNeedExpirationDateAttribute()
    {
       return $this->spec->product->category->need_expiration_date;
    }

    public  function getNeedExpirationDateNameAttribute()
    {
        return $this->spec->product->category->need_expiration_date?"保质期":"";
    }

    public  function getNeedBestBeforeDateAttribute()
    {
        return $this->spec->product->category->need_best_before_date;

    }

    public  function getNeedBestBeforeDateNameAttribute()
    {
        return $this->spec->product->category->need_best_before_date?"最佳使用期":"";

    }

    public  function getNeedProductionBatchNumberAttribute()
    {
        return $this->spec->product->category->need_production_batch_number;
    }

    public  function getNeedProductionBatchNumberNameAttribute()
    {
        return $this->spec->product->category->need_production_batch_number?"生产批次号":"";
    }

    public  function  getProductNameAttribute()
    {
        $name = $this->spec?($this->spec->product_name?:""):"";
        return $name;
    }

    public  function  getProductNameEnAttribute()
    {
        $name = $this->spec?($this->spec->product_name_en?:""):"";
        return $name;
    }

    public  function  getProductNameCnAttribute()
    {
        $name = $this->spec?($this->spec->product_name_cn?:""):"";
        return $name;
    }



    /**
     * @return string
     */
    public function getRelevanceCodeBarcodeAttribute()
    {
        return 'data:image/png;base64,' . app("DNS1D")->getBarcodePNG($this->relevance_code, "C128");
    }

    /**
     * @return \App\Models\WarehouseLocation|null
     */
    public function getRecommendedLocationAttribute()
    {
        return $this->recommendLocation();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\App\Models\WarehouseLocation|null
     */
    public function getRecommendedLocationsAttribute()
    {
        return $this->recommendLocations();
    }

    /**
     * @return integer
     */
    public function getEditCountAttribute()
    {
        return $this->logs()->where('type_id', ProductStockLog::TYPE_COUNT)->count();
    }

    /**
     * 待上架库存
     *
     * @return int|mixed
     */
//    public function getShelfNumWaitingAttribute()
//    {
//        return $this->status == ProductStock::GOODS_STATUS_PREPARE
//        && in_array($this->batch->status, [
//            Batch::STATUS_PROCEED, Batch::STATUS_ACCOMPLISH
//        ])
//            ? $this->stockin_num
//            : 0;
//    }

    /**
     * 获得待验货数量
     *
     * 所有拣货单已完成拣货，待验货状态,
     * 待验货=总的应捡数量-已验货数量
     *
     * @return int
     */
//    public function getToBeVerifyAttribute()
//    {
//        $verifying_num = OrderItem::where('product_stock_id', $this->id)
//                        ->where('verify_num',0)
//                        ->selectRaw('sum(pick_num - verify_num) AS verifying_num')
//                        ->value('verifying_num') ?? 0;
//
//        return $verifying_num;
//    }

    /*
    |--------------------------------------------------------------------------
    | Operations
    |--------------------------------------------------------------------------
    */

    public function recommendLocations()
    {
        $ids = ProductStock::has('location')
            ->enabled()
            ->where('spec_id', $this->spec_id)
            ->where('id', '!=', $this->id)
            ->latest()
            ->pluck('warehouse_location_id')
            ->toArray();

        $locations = WarehouseLocation::findMany($ids);

        return $locations;
    }

    /**
     * 同步更新库存基础信息至货位
     * 
     */
    public function syncLocationInfo()
    {
        ProductStockLocation::where('stock_id', $this->id)
          ->update(
            [
                'ean'                       => $this->ean,
            ]
        );
    }

    /**
     * 推到货位上面
     * 将库存上架
     */
    public function pushToLocation(int $locationId = 0, $qty = 0)
    {

        //增加到货位上面（虚拟的）
        $location = new ProductStockLocation;
        $location->stock_id = $this->id;
        $location->spec_id  = $this->spec_id;
        $location->warehouse_location_id = $locationId; //实际仓库货位
        $location->sku = $this->sku;
        $location->ean = $this->ean;
        $location->relevance_code = $this->relevance_code;
        $location->shelf_num = $qty;
        $location->owner_id = $this->owner_id;
        $location->warehouse_id = $this->warehouse_id;
        $location->sort_num = 0;

        $location->save();

        return $location;
    }




}
