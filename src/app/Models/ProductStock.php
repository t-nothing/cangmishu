<?php

namespace App\Models;

use App\Models\Model;
use App\Models\ProductSpec;
use App\Models\Product;
use App\Models\WarehouseLocation;

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

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

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
    public function addLog($type, $operation_num, $order_sn = '', $sku_total_shelf_num_old = 0, $remark = '')
    {
        switch ($type) {
            case ProductStockLog::TYPE_BATCH:
                $order_sn = $this->batch->batch_code;
                break;
            case ProductStockLog::TYPE_SHELF:
                $order_sn = $this->batch->batch_code;
                break;
            default:
                break;
        }

        $sku_total_stockin_num = ProductStock::where('sku', $this->sku)
            ->ofWarehouse($this->warehouse_id)
            ->whose($this->owner_id)
            ->where(function ($query) {
                $query->enabled()->orWhere(function ($query) {
                    $query->where('status', ProductStock::GOODS_STATUS_PREPARE)->whereHas('batch', function ($query) {
                        $query->where('status', Batch::STATUS_PROCEED)->orWhere('status', Batch::STATUS_ACCOMPLISH);
                    });
                });
            })
            ->sum('stockin_num');

        $spec_total_stockin_num = ProductStock::where('spec_id', $this->spec_id)
            ->ofWarehouse($this->warehouse_id)
            ->whose($this->owner_id)
            ->where(function ($query) {
                $query->enabled()->orWhere(function ($query) {
                    $query->where('status', ProductStock::GOODS_STATUS_PREPARE)->whereHas('batch', function ($query) {
                        $query->where('status', Batch::STATUS_PROCEED)->orWhere('status', Batch::STATUS_ACCOMPLISH);
                    });
                });
            })
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
        // 特定SKU的仓库数量 = 此SKU剩余已上架数量 + 此SKU待验货数量

        // 待验货数量
        $verifying_num = OrderItem::where('product_stock_id', $this->id)
            ->whereHas('pick', function($query) {
                $query->where('status', Pick::STATUS_PICK_DONE);
            })
            ->selectRaw('sum(pick_num - verify_num) AS verifying_num')
            ->value('verifying_num') ?? 0;

        $stockin_num = $new_shelf_num + $verifying_num;

        return $stockin_num;
    }

    public function getCurrentLockNum()
    {
        // 特定SKU的仓库数量 = 此SKU剩余已上架数量 + 此SKU待验货数量

        // 待验货数量
        $lock_num = OrderItem::ofWarehouse($this->warehouse_id)
            ->whose($this->owner_id)
            ->where('relevance_code', $this->relevance_code)
            ->whereHas('pick', function($query) {
                $query->whereIn('status', [
                    Pick::STATUS_DEFAULT,
                    Pick::STATUS_PICKING,
                    Pick::STATUS_PICK_DONE,
                ]);
            })
            ->sum('amount');

        return $lock_num;
    }
}
