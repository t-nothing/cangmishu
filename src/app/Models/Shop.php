<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use SoftDeletes;
    protected $table = 'shop';

    protected $hidden = ['domain','owner_id','deleted_at','announcement_cn', 'announcement_en', 'pay_notice_cn', 'pay_notice_en', 'cart_notice_cn', 'cart_notice_en', 'is_stock_show', 'is_price_show', 'is_allow_over_order', 'email'];
    protected $appends = [
        'name',
        'remark',
        'currency'
    ];

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        
        $lang = app('translator')->getLocale();
        
        return $this->{'name_'.$lang}??$this->name_cn;
    }

    public function getRemarkAttribute()
    {
        
        $lang = app('translator')->getLocale();
        
        return $this->{'remark_'.$lang}??$this->name_cn;
    }

    public function getCurrencyAttribute()
    {
        
        $result = "￥";
        if($this->default_currency == "USD") {
            $result = "$";
        }elseif($this->default_currency == "EUR") {
            $result = "€";
        }
        
        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */


    public function paymentMethod()
    {
        return $this->hasMany('App\Models\ShopPaymentMethod', 'shop_id', 'id' );
    }

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

    public function senderAddress()
    {
        return $this->hasOne('App\Models\ShopSenderAddress', 'shop_id', 'id');
    }

    public function items()
    {
        return $this->hasMany('App\Models\ShopProduct', 'shop_id', 'id' );
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
