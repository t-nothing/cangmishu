<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $table = 'product';
    protected $fillable = ['warehouse_id', 'owner_id', 'name_cn', 'name_en', 'category_id','remark','photos'];

    const PRODUCT_STATUS_PREPARE = 1; // 待入库的
    const PRODUCT_STATUS_ONLINE = 2; // 正常商品，可以售卖
    const PRODUCT_STATUS_OFFLINE = 3; // 在仓库，不卖了，下架了

    public   $appends= ['stockin_num'];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function productInfo()
    {
        return $this->hasManyThrough(
            'App\Models\ProductStock',
            'App\Models\ProductSpec',
            'id',
            'spec_id',
            'id',
            'id'
        );
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id', 'id');
    }

    public function specs()
    {
        return $this->hasMany('App\Models\ProductSpec', 'product_id', 'id' );
    }

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * 限制查询属于指定用户。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhose($query, $user_id)
    {
        return $query->where('owner_id', $user_id);
    }



    /**
     * 限制查询属于指定仓库。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfWarehouse($query, $user_id)
    {
        return $query->where('warehouse_id', $user_id);
    }

    /**
     * 限制查询只包括指定关键字。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @author liusen
     */
    public function scopeHasKeyword($query, $keywords)
    {
        $query->where(function($q) use ($keywords){
             return $q->whereHas('specs',function ($qq) use ($keywords){
                        $qq->where('relevance_code', 'like','%' .$keywords . '%');
                    })
                    ->orwhere('name_cn', 'like', '%' . $keywords . '%')
                    ->orWhere('name_en', 'like', '%' . $keywords . '%');
        });
        return $query;
    }

    public function getStockInNumAttribute()
    {
        return 0;
    }

    static function stock($id)
    {
        return $product = Product::find($id)->with('specs.stocks');
    }
}
