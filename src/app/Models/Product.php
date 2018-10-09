<?php

namespace App\Models;

use App\Models\Model;
use App\Models\WarehouseEmployee;

class Product extends Model
{
    protected $table = 'product';

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
        return $query->where('owner_id', $user_id);
    }

    /**
     * 限制查询只包括指定关键字。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @author liusen
     */
    public function scopeHasKeyword($query, $keywords)
    {
	$ids = ProductSpec::ofWarehouse(app('auth')->warehouse()->id)
		->where('relevance_code', 'like','%' .$keywords . '%');

	if (app('auth')->roleId() == WarehouseEmployee::ROLE_RENTER){
		$ids->whose(app('auth')->id());
	}
        $query->where('name_cn', 'like', '%' . $keywords . '%')
                     ->orWhere('name_en', 'like', '%' . $keywords . '%')
                     ->orWhere('hs_code', 'like', '%' . $keywords . '%')
		     ->orWhere('origin', 'like', '%' . $keywords . '%');

	$ids = $ids->pluck('product_id')->toArray();
	if (!empty($ids)) {
		$query->orWhereIn('id', $ids);
	}
	return $query;
    }


    static function stock($id)
    {
        return $product = Product::find($id)->with('specs.stocks');
    }
}
