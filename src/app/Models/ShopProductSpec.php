<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
