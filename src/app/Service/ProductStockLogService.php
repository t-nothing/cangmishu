<?php
namespace  App\Services\Service;

use App\Models\ProductStockLog;
use App\Models\ProductStock;

class ProductStockLogService
{
    protected  $warehouse;
    protected  $typeId;
    protected  $stock;
    protected  $num;
    protected  $remark;
    protected  $itemId = 0;
    protected  $source = 'web';

    public function __construct()
    {
        $this->warehouse = app('auth')->warehouse();
    }

    public function setTypeId($v)
    {
        $this->typeId = $v;
        return $this;
    }

    public function getTypeId()
    {
        return $this->typeId;
    }

    public function setStock($v)
    {
        $this->stock = $v;
        return $this;
    }

    public function getStock()
    {
        return $this->stock;
    }

    public function setNum($v)
    {
        $this->num = $v;
        return $this;
    }

    public function getNum()
    {
        return $this->num;
    }


    public function setItemId($v)
    {
        $this->itemId = $v;
        return $this;
    }

    public function getItemId()
    {
        return $this->itemId;
    }


    public function setRemark($v)
    {
        $this->remark = $v;
        return $this;
    }

    public function getRemark()
    {
        return $this->remark;
    }

    public function setSource($v)
    {
        $this->source = $v;
        return $this;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function log()
    {
        $stock = $this->getStock();


        app("log")->info("log开始记录");

        //记录
        $model  = new ProductStockLog;
        $model->product_stock_id    = $stock->id;
        $model->type_id             = $this->getTypeId();
        $model->owner_id            = $stock->owner_id;
        $model->warehouse_id        = $stock->warehouse_id;
        $model->spec_id             = $stock->spec_id;
        $model->sku                 = $stock->sku;
        $model->operation_num       = $this->getNum();
        $model->remark              = $this->getRemark();
        $model->item_id             = $this->getItemId();
        $model->operator            = app('auth')->id();
        $model->source              = $this->getSource();

        $model->product_total_stock_num = $stock->spec->product->total_stock_num;
        $model->spec_total_stock_num = $stock->spec->total_stock_num;
        $model->stock_total_stock_num = $stock->stock_num;

        //库存记录

        $model->save();
    }
}