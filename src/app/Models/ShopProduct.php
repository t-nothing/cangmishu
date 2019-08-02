<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class ShopProduct extends Model
{
    protected $table = 'shop_product';
    protected  $fillable = ['product_id','sale_price','is_shelf','remark','pics'];
    protected $casts = [
        'pics' => 'array'
    ]; 

    public function shop()
    {
        return $this->belongsTo('App\Models\Shop', 'shop_id', 'id');
    }
}
