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

    public function log()
    {
        $stock = $this->getStock();
        //记录
        $model = new ProductStockLog;
        $model->product_stock_id = $stock->id;
        $model->type_id = $this->getTypeId();
        $model->owner_id = 1;
        $model->warehouse_id = $stock->warehouse_id;
        $model->spec_id = $stock->spec_id;
        $model->sku = $stock->sku;
        $model->operation_num = $this->getNum();
        $model->remark = $this->getRemark();
        $model->item_id = $this->getItemId();
        $model->operator = app('auth')->id();

        $model->save();
    }
}