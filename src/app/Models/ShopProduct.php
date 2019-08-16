<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class ShopProduct extends Model
{
    protected $table = 'shop_product';
    protected  $fillable = ['product_id','sale_price','is_shelf','remark','pics'];
    protected $casts = [
        'pics' => 'array',
        'descs'=> 'array',
    ]; 

    protected $appends = ['name'];

    public function shop()
    {
        return $this->belongsTo('App\Models\Shop', 'shop_id', 'id');
    }
    
    public function specs()
    {
        return $this->hasMany('App\Models\ShopProductSpec', 'shop_product_id', 'id');
    }


    /**
     * @return string
     */
    public function getNameAttribute()
    {
        $name = '';
        $lang = app('translator')->locale()?:'cn';
        if($lang == 'zh-CN'){
            $lang='cn';
        }
        return $this->{'name_'.$lang};
    }

    /**
     * 限制查询只包括指定关键字。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @author liusen
     */
    public function scopeHasKeyword($query, $keywords)
    {
        $query->orwhere('shop_product.name_cn', 'like', '%' . $keywords . '%')->orWhere('shop_product.name_en', 'like', '%' . $keywords . '%');
    }
}
