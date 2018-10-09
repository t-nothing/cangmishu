<?php

namespace App\Models;

use App\Models\Model;
use App\Models\ProductStock;

class ProductSku extends Model
{
    protected $table = 'product_sku';

    public function insertData($data)
    {
        return $this->insertGetId($data);
    }

    // public function exists(ProductStock $stock)
    // {
    // 	$stock
    // }
}
