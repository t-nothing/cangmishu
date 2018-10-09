<?php

namespace App\Models;

use App\Models\Model;

class ProductOrigin extends Model
{
    protected $table = 'product_origin';

//    public function product()
//    {
//        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
//    }
//
//    public function stocks()
//    {
//        return $this->hasMany('App\Models\ProductStock', 'spec_id', 'id');
//    }
//
//    public function skus()
//    {
//        return $this->hasMany('App\Models\ProductSku', 'spec_id', 'id');
//    }
//
//    public function stocksWarehouse()
//    {
//        return $this->hasMany('App\Models\ProductStock', 'spec_id', 'id')
//            ->with('warehouse');
//    }
//
//    public function stockLog()
//    {
//        return $this->hasMany('App\Models\ProductStockLog', 'spec_id', 'id');
//    }
    /*
        |--------------------------------------------------------------------------
        | Scopes
        |--------------------------------------------------------------------------
        */

//    /**
//     * 限制查询属于指定用户。
//     *
//     * @return \Illuminate\Database\Eloquent\Builder
//     */
//    public function scopeWhose($query, $user_id)
//    {
//        return $query->where('owner_id', $user_id);
//    }

}
