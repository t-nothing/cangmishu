<?php

namespace App\Models;

use App\Models\ProductStock;
use App\Models\ProductSku;
use App\Models\ProductSpec;
use Illuminate\Support\Facades\DB;

class BatchProduct extends Model
{
    
    protected $table = 'batch_product';

    public     $timestamps = true;

    protected $guarded  =[];

    /**
     * 过期时间
     */
    protected $expirationDate;
    /**
     * 最佳食用期
     */
    protected $bestBeforeDate;
    /**
     * 库存数量
     */
    protected $stockQty;
    /**
     * 箱子号
     */
    protected $boxCode;
    /**
     * 供应商货号
     */
    protected $distributorCode;
    /**
     * EAN
     */
    protected $ean;
    /**
     * 商品出厂批次号
     */
    protected $productionBatchNumber;
    /**
     * 上架位置
     */
    protected $locationId = 0;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'relevance_code_barcode',
        'product_name'
    ];


    public function setExpirationDate($v)
    {
        $this->expirationDate = $v;
        return $this;
    }

    public function getExpirationDate()
    {
        return empty($this->expirationDate)?NULL:$this->expirationDate;
    }


    public function setBestBeforeDate($v)
    {
        $this->bestBeforeDate = $v;
        return $this;
    }

    public function getBestBeforeDate()
    {
        return empty($this->bestBeforeDate)?NULL:$this->bestBeforeDate;
    }


    public function setStockQty($v)
    {
        $this->stockQty = $v;
        return $this;
    }

    public function getStockQty()
    {
        return $this->stockQty;
    }

    public function setBoxCode($v)
    {
        $this->boxCode = $v;
        return $this;
    }

    public function getBoxCode()
    {
        return $this->boxCode;
    }


    public function setDistributorCode($v)
    {
        $this->distributorCode = $v;
        return $this;
    }

    public function getDistributorCode()
    {
        return $this->distributorCode;
    }

    public function setEan($v)
    {
        $this->ean = $v;
        return $this;
    }

    public function getEan()
    {
        return $this->ean;
    }

    public function setProductionBatchNumber($v)
    {
        $this->productionBatchNumber = $v;
        return $this;
    }

    public function getProductionBatchNumber()
    {
        return $this->productionBatchNumber;
    }

    public function setLocationId($v)
    {
        $this->locationId = $v;
        return $this;
    }

    public function getLocationId()
    {
        return $this->locationId;
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public  function  getProductNameAttribute()
    {
        $name = $this->spec?($this->spec->product_name?:""):"";
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
     * @return string
     */
    public function getSkuBarcodeAttribute()
    {
        return 'data:image/png;base64,' . app("DNS1D")->getBarcodePNG($this->sku, "C128");
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function stocks()
    {
        return $this->hasMany('App\Models\ProductStock', 'batch_id', 'batch_id');
    }

    public function batch()
    {
        return $this->belongsTo('App\Models\Batch', 'batch_id', 'id');
    }

    public function spec()
    {
        return $this->belongsTo('App\Models\ProductSpec', 'spec_id', 'id');
    }

    /**
     * @return \App\Models\WarehouseLocation|null
     */
    public function getRecommendedLocationAttribute()
    {
        return null;
    }

    /**
     * 将入库单明细入库
     */
    public function convertToStock()
    {
        $stock = new ProductStock;
        //真正写入到库存表中

        $stock->owner_id                  = $this->owner_id;
        $stock->spec_id                   = $this->spec_id;
        $stock->relevance_code            = $this->relevance_code;
        $stock->ean                       = $this->getEan();//EANean码
        $stock->box_code                  = $this->getBoxCode(); //箱子编码
        $stock->need_num                  = $this->getStockQty();//入库
        $stock->pieces_num                = $this->pieces_num;
        $stock->remark                    = $this->remark;
        $stock->purchase_price            = $this->purchase_price;  
        $stock->purchase_currency         = $this->purchase_currency;  
                // distributor_id   = $this->distributor_id;
        $stock->distributor_code          = $this->getDistributorCode();
        $stock->warehouse_id              = $this->warehouse_id;
        $stock->batch_id                  = $this->batch_id;
        $stock->status                    = Product::PRODUCT_STATUS_ONLINE;
        $stock->sku                       = $this->sku; //ProductSpec::newSku($this->spec);
        $stock->expiration_date           = $this->getExpirationDate(); //保质期
        $stock->best_before_date          = $this->getBestBeforeDate();
        $stock->production_batch_number   = $this->getProductionBatchNumber();
        $stock->warehouse_location_id     = $this->getLocationId();

        //
        $stock->stock_num                 = $this->getStockQty();
        $stock->stockin_num               = $this->getStockQty();

        $stock->save();
        //推送上默认位置

        return $stock;
    }
}