<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class ShopProductSpec extends Model
{
    protected $table = 'shop_product_spec';
    protected  $fillable = ['id', 'shop_id','product_id','shop_product_id','spec_id','sale_price','is_shelf', 'name_cn', 'name_en'];

    public   $appends= ['name'];

    public function product()
    {
        return $this->belongsTo('App\Models\ShopProduct', 'shop_product_id', 'id');
    }

    public function productSpec()
    {
        return $this->hasOne('App\Models\ProductSpec', 'id', 'spec_id');
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        $lang = app('translator')->locale()?:'cn';
        if($lang == 'zh-CN'){
            $lang='cn';
        }

        return $this->{'name_'.$lang};
    }
}
