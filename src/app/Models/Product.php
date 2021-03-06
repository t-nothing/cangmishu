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

class Product extends Model
{
    use SoftDeletes;
    protected $table = 'product';
    protected $fillable = ['warehouse_id', 'owner_id', 'name_cn', 'name_en', 'category_id','remark','photos', 'sale_price', 'purchase_price'];

    const PRODUCT_STATUS_PREPARE = 1; // 待入库的
    const PRODUCT_STATUS_ONLINE = 2; // 正常商品，可以售卖
    const PRODUCT_STATUS_OFFLINE = 3; // 在仓库，不卖了，下架了

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
        return $query->where('product.owner_id', $user_id);
    }



    /**
     * 限制查询属于指定仓库。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfWarehouse($query, $user_id)
    {
        return $query->where('product.warehouse_id', $user_id);
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
                    ->orwhere('product.name_cn', 'like', '%' . $keywords . '%')
                    ->orWhere('product.barcode', 'like', $keywords . '%');
        });
        return $query;
    }

    static function stock($id)
    {
        return $product = Product::find($id)->with('specs.stocks');
    }
}
