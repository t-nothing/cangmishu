<?php
namespace  App\Services\Service;

use App\Models\Product;

class ProductService
{
    public function getProductIdByNameCn($name) {
        $product = Product::where('name_cn', $name)
            ->whose(app('auth')->ownerId())
            ->first();
        if(!$product){
            return 0;
        }
        return $product->id;
    }
}