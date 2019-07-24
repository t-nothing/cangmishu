<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class ShopProduct extends Model
{
    use SoftDeletes;
    protected $table = 'shop_product';


    public function shop()
    {
        return $this->belongsTo('App\Models\Shop', 'shop_id', 'id');
    }

    public function senderAddress()
    {
        return $this->hasOne('App\Models\ShopSenderAddress', 'shop_id', 'id');
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


    static function stock($id)
    {
        return $product = Product::find($id)->with('specs.stocks');
    }
}
