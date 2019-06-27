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
        'expiration_date',
        'best_before_date',
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

    public function location()
    {
        return $this->belongsTo('App\Models\WarehouseLocation', 'warehouse_location_id', 'id');
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

    public  function getNeedBestBeforeDateAttribute()
    {
        return $this->spec->product->category->need_best_before_date;

    }

    public  function getNeedProductionBatchNumberAttribute()
    {
        return $this->spec->product->category->need_production_batch_number;
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

    /**
     * 生成 SKU
     *
     * @return bool
     */
    public function generateSKU()
    {
        if (empty(trim($this->sku))) {
            // $this->sku = 'sku' . substr(md5($this->stock_id), 0, 9);
            $this->sku = 'SKU'. $this->warehouse->code .encodeData(date('Y-m-d')) . encodeseq($this->id);

            return $this->save();
        }

        return true;
    }

    /**
     * 添加数据
     */
    public function addLog($type, $operation_num, $sku_total_shelf_num_old = 0, $remark = '')
    {
        switch ($type) {
            case ProductStockLog::TYPE_BATCH_SHELF:
                $order_sn = $this->batch->batch_code;
                break;
            case ProductStockLog::TYPE_OUTPUT:
                $order_sn = $this->order->out_sn;
                break;
            default:
                $order_sn = "";
                break;
        }

        $sku_total_stockin_num = ProductStock::where('sku', $this->sku)
            ->ofWarehouse($this->warehouse_id)
            ->whose($this->owner_id)
            ->enabled()
            ->sum('stockin_num');

        $spec_total_stockin_num = ProductStock::where('spec_id', $this->spec_id)
            ->ofWarehouse($this->warehouse_id)
            ->whose($this->owner_id)
            ->enabled()
            ->sum('stockin_num');

        $sku_total_shelf_num  = ProductStock::where('sku', $this->sku)
            ->ofWarehouse($this->warehouse_id)
            ->whose($this->owner_id)
            ->enabled()
            ->sum('shelf_num');

        $spec_total_shelf_num = ProductStock::where('spec_id', $this->spec_id)
            ->ofWarehouse($this->warehouse_id)
            ->whose($this->owner_id)
            ->enabled()
            ->sum('shelf_num');
        //根据uri判断时桌面端还是手持端

        if($type == ProductStockLog::TYPE_OUTPUT){
            $log = ProductStockLog::where('order_sn', $order_sn)->where('type_id',$type)->where('product_stock_id',$this->id)->first();
            if(!empty($log)){
                app('log')->info('重复拣货失败',['log'=>$log]);
                throw new BusinessException('拣货单'.$order_sn.'商品'.$this->relevance_code.'重复拣货');
            }
        }
        return $this->logs()->create([
            'type_id'              => $type,
            'order_sn'             => $order_sn,
            'owner_id'             => $this->owner_id,
            'warehouse_id'         => $this->warehouse_id,
            'spec_id'              => $this->spec_id,
            'sku'                  => $this->sku,
            'operation_num'        => $operation_num,
            'spec_total_stockin_num' => $spec_total_stockin_num,
            'spec_total_shelf_num' => $spec_total_shelf_num,
            'sku_total_stockin_num' => $sku_total_stockin_num,
            'sku_total_shelf_num'  => $sku_total_shelf_num,
            'sku_total_shelf_num_old' => $sku_total_shelf_num_old,
            'operator'             => app('auth')->id(),
            'remark'               => $remark,
        ]);
    }

    public function recommendLocation()
    {
        $stock = ProductStock::with('location')
            ->enabled()
            ->where('spec_id', $this->spec_id)
            ->where('id', '!=', $this->id)
            ->has('location')
            ->latest()
            ->first();

        return $stock ? $stock->location : null;
    }

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

    public function getCurrentStockinNum($new_shelf_num)
    {
        // 特定SKU的仓库数量 = 此SKU剩余已上架数量 + 此SKU待拣货数量

        // 待验货数量
//        $verifying_num =OrderItem::ofWarehouse($this->warehouse_id)
//        ->whose($this->owner_id)
//        ->where('relevance_code', $this->relevance_code)
//        ->where('product_stock_id', $this->getKey())
//        ->where('verify_num',0)
//        ->where('pick_num',0)
//        ->sum('amount');
        $stockin_num = $new_shelf_num ;

        return $stockin_num;
    }

//    public function getCurrentLockNum()
//    {
//
//        // 待验货数量
//        $lock_num = OrderItem::ofWarehouse($this->warehouse_id)
//            ->whose($this->owner_id)
//            ->where('relevance_code', $this->relevance_code)
//            ->where('product_stock_id', $this->getKey())
//            ->whereHas('pick', function($query) {
//                $query->whereIn('status', [
//                    Pick::STATUS_DEFAULT,
//                    Pick::STATUS_PICKING,
//                    Pick::STATUS_PICK_DONE,
//                ]);
//            })
//            ->sum('amount');
//
//        return $lock_num;
//    }

    /**
     * 获取现在总库存
     * @return mixed
     */
    public function getStockinNum()
    {
        return   self::whose($this->owner_id)
            ->ofWarehouse($this->warehouse_id)
            ->enabled()
            ->where('relevance_code', $this->relevance_code)
            ->sum('stockin_num');
    }

}
