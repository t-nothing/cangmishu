<?php

namespace App\Models;

use App\Models\Model;
use App\Models\SkuMarkLog;
use Illuminate\Support\Facades\Redis;

class ProductSpec extends Model
{
	protected $table = 'product_spec';

	/*
	|--------------------------------------------------------------------------
	| Relations
	|--------------------------------------------------------------------------
	*/

	public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

	public function product()
	{
		return $this->belongsTo('App\Models\Product', 'product_id', 'id');
	}

	public function stocks()
	{
		return $this->hasMany('App\Models\ProductStock', 'spec_id', 'id');
	}

	public function skus()
	{
		return $this->hasMany('App\Models\ProductSku', 'spec_id', 'id');
	}

	public function stocksWarehouse()
	{
		return $this->hasMany('App\Models\ProductStock', 'spec_id', 'id')->with('warehouse');
	}

	public function stockLog()
	{
		return $this->hasMany('App\Models\ProductStockLog', 'spec_id', 'id');
	}

	/*
	|--------------------------------------------------------------------------
	| Attributes
	|--------------------------------------------------------------------------
	*/

	/**
	 * @return string
	 */
	public function getProductNameAttribute()
	{
		$name = '';

		if (isset($this->product)) {
			$name = $this->product['name_'.app('translator')->locale()]
				. '('
				. $this->{'name_'.app('translator')->locale()}
				. ')';
		}

		return $name;
	}

	/**
	 * @return string
	 */
	public function getProductNameCnAttribute()
	{
		return isset($this->product)
			? $name = $this->product['name_cn'] . '(' . $this->name_cn . ')'
			: '';
	}

	/**
	 * @return string
	 */
	public function getProductNameEnAttribute()
	{
		return isset($this->product)
			? $name = $this->product['name_en'] . '(' . $this->name_en . ')'
			: '';
	}

	/**
	 * 某规格/某仓库/仓库库存 = 已上架 + 已入库但未上架
	 *
	 * @return integer
	 */
	public function getStockInWarehouseAttribute()
	{
		$owner_id = $this->owner_id;

		return ProductStock::ofWarehouse($this->warehouse_id)
			->when(app('auth')->isRenter(), function ($query) use ($owner_id) {
        		$query->whose($owner_id);
        	})
			->whereIn('status', [
				ProductStock::GOODS_STATUS_PREPARE,
				ProductStock::GOODS_STATUS_ONLINE,
			])
			->where('spec_id', $this->id)
			->sum('stockin_num');
	}

	/**
	 * 某规格/某仓库/已上架
	 *
	 * @return integer
	 */
	public function getStockOnShelfAttribute()
	{
		$owner_id = $this->owner_id;

		return ProductStock::ofWarehouse($this->warehouse_id)
			->when(app('auth')->isRenter(), function ($query) use ($owner_id) {
        		$query->whose($owner_id);
        	})
			->enabled()
			->where('spec_id', $this->id)
			->sum('shelf_num');
	}

	/**
	 * 待上架库存
	 *
	 * @return integer
	 */
	public function getStockToBeOnShelfAttribute()
	{
		$owner_id = $this->owner_id;

		return ProductStock::ofWarehouse($this->warehouse_id)
			->when(app('auth')->isRenter(), function ($query) use ($owner_id) {
        		$query->whose($owner_id);
        	})
            ->where('spec_id', $this->id)
            ->where('status', ProductStock::GOODS_STATUS_PREPARE)
            ->whereHas('batch', function ($query) {
                $query->where('status', Batch::STATUS_PROCEED)
                      ->orWhere('status', Batch::STATUS_ACCOMPLISH);
            })->sum('total_stockin_num');
	}

	/**
	 * 入库次数
	 *
	 * @return integer
	 */
	public function getStockEntranceTimesAttribute()
	{
		$owner_id = $this->owner_id;

		return ProductStock::ofWarehouse($this->warehouse_id)
			->when(app('auth')->isRenter(), function ($query) use ($owner_id) {
        		$query->whose($owner_id);
        	})
			->whereIn('status', [
				ProductStock::GOODS_STATUS_PREPARE,
				ProductStock::GOODS_STATUS_ONLINE,
			])
			->where('spec_id', $this->id)
			->where('total_stockin_num', '>', 0)
			->count();
	}

	/**
	 * 出库次数
	 *
	 * @return integer
	 */
	public function getStockOutTimesAttribute()
	{
		$owner_id = $this->owner_id;

		return OrderItem::ofWarehouse($this->warehouse_id)
			->when(app('auth')->isRenter(), function ($query) use ($owner_id) {
        		$query->whose($owner_id);
        	})
            ->where('relevance_code', $this->relevance_code)
            ->whereHas('order', function ($query) {
                $query->where('status', '>=', Order::STATUS_WAITING);
            })->count();
	}

	/**
	 * 入库数量
	 *
	 * @return integer
	 */
	public function getStockEntranceQtyAttribute()
	{
		$owner_id = $this->owner_id;

		return ProductStock::ofWarehouse($this->warehouse_id)
			->when(app('auth')->isRenter(), function ($query) use ($owner_id) {
        		$query->whose($owner_id);
        	})
			->whereIn('status', [
				ProductStock::GOODS_STATUS_PREPARE,
				ProductStock::GOODS_STATUS_ONLINE,
			])
			->where('spec_id', $this->id)
			->sum('total_stockin_num');
	}

	/**
	 * 出库数量
	 *
	 * @return integer
	 */
	public function getStockOutQtyAttribute()
	{
		$owner_id = $this->owner_id;

		return OrderItem::ofWarehouse($this->warehouse_id)
			->when(app('auth')->isRenter(), function ($query) use ($owner_id) {
        		$query->whose($owner_id);
        	})
            ->where('relevance_code', $this->relevance_code)
            ->whereHas('order', function ($query) {
                $query->where('status', '>=', Order::STATUS_WAITING);
            })
            ->sum('verify_num');
	}

	/**
	 * 锁定库存
	 *
	 * @return integer
	 */
	public function getReservedNumAttribute()
    {
        // 特定SKU的仓库数量 = 此SKU剩余已上架数量 + 此SKU待验货数量

    	$owner_id = $this->owner_id;

        // 待验货数量
        $lock_num = OrderItem::ofWarehouse($this->warehouse_id)
        	->when(app('auth')->isRenter(), function ($query) use ($owner_id) {
        		$query->whose($owner_id);
        	})
            ->where('relevance_code', $this->relevance_code)
            ->whereHas('pick', function($query) {
                $query->whereIn('status', [
                    Pick::STATUS_DEFAULT,
                    Pick::STATUS_PICKING,
                    Pick::STATUS_PICK_DONE,
                ]);
            })
            ->sum('amount');

        return $lock_num;
    }

    /**
	 * 可用库存
	 *
	 * @return integer
	 */
	public function getAvailableNumAttribute()
    {
        return max($this->stock_in_warehouse - $this->reserved_num, 0);
    }

	/*
	|--------------------------------------------------------------------------
	| Scopes
	|--------------------------------------------------------------------------
	*/

	/**
	 * 限制查询只包括指定关键字。
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeHasKeyword($query, $keywords)
	{
		$query->ofWarehouse(app('auth')->warehouse()->id)->where('relevance_code', '=', $keywords);

		if (app('auth')->roleId() == WarehouseEmployee::ROLE_RENTER) {
			$query->whose(app('auth')->id());
		}

		// 可以根据货品信息搜索
		$product = Product::ofWarehouse(app('auth')->warehouse()->id);

		if (app('auth')->roleId() == WarehouseEmployee::ROLE_RENTER) {
			$product->whose(app('auth')->id());
		}

		$product_ids = $product->where('name_cn', 'like', '%' . $keywords . '%')
			->orWhere('name_en', 'like', '%' . $keywords . '%')
			->orWhere('hs_code', 'like', '%' . $keywords . '%')
			->orWhere('origin', 'like', '%' . $keywords . '%')
			->pluck('id');

		if ($product_ids) {
			$query->orWhereIn('product_id', $product_ids);
		}

		return $query;
	}

	/**
	 * 限制查询只包括指定SKU。
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeHasSku($query, $sku)
	{
		return $query->whereHas('stocks', function ($query) use ($sku) {
			$query->where('sku', $sku);
		});
	}

	/**
	 * 限制查询只包括指定货品名。
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeHasProductName($query, $name)
	{
		return $query->whereHas('product', function ($query) use ($name) {
			$query->where('name_cn', 'like', "%{$name}%")->orWhere('name_en', 'like', "%{$name}%");
		});
	}

	/**
	 * 限制查询只包括指定生产批次号。
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeHasProductBatchNumber($query, $production_batch_number)
	{
		return $query->whereHas('stocks', function ($query) use ($production_batch_number) {
			$query->where('production_batch_number', $production_batch_number);
		});
	}

	/**
	 * 已盘点过的。
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeHasEverEdited($query)
	{
		return $query->whereHas('stockLog', function ($query) {
			$query->where('type_id', ProductStockLog::TYPE_COUNT);
		});
	}

	/**
	 * 盘点过的。
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeOnlyEdited($query)
	{
		return $query->whereHas('stockLog', function ($query) {
			$query->where('type_id', ProductStockLog::TYPE_COUNT);
		});
	}

	/**
	 * 从未盘点过的。
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeOnlyNeverEdited($query)
	{
		return $query->whereDoesntHave('stockLog', function ($query) {
			$query->where('type_id', ProductStockLog::TYPE_COUNT);
		});
	}

	/**
	 * 待上架的。
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeOnlyToBeOnShelf($query, $warehouse_id, $owner_id)
	{
		return $query->whereHas('stocks', function ($query) use($warehouse_id, $owner_id) {
			$query->ofWarehouse($warehouse_id)
				->when(app('auth')->isRenter(), function ($query) use($owner_id) {
					$query->whose($owner_id);
				})
				->where('status', ProductStock::GOODS_STATUS_PREPARE)
				->whereHas('batch', function ($query) {
				    $query->where('status', Batch::STATUS_PROCEED)->orWhere('status', Batch::STATUS_ACCOMPLISH);
				});
		});
	}

	/*
	|--------------------------------------------------------------------------
	| Operations
	|--------------------------------------------------------------------------
	*/

	static function newSku($spec)
	{
	  $warehouse_code = app('auth')->warehouse()->code;
	  $category_id    = $spec->product->category_id;
	  $spec_id        = $spec->id;
	  $redis_key      = 'wms_spec'.$spec_id;
	  $is_exists      =  Redis::Exists($redis_key);
	  if(!$is_exists){
		  $sku_mark = SkuMarkLog::where('warehouse_code',$warehouse_code)->where('spec_id',$spec_id)->orderBy('id')->pluck('sku_mark')->first();
		  $sku_mark = empty($sku_mark) ? 1 :$sku_mark;
		  Redis::set($redis_key,$sku_mark);
	  }
	  $skuLog = new SkuMarkLog();
	  $skuLog->warehouse_code = $warehouse_code;
	  $skuLog->spec_id        = $spec_id;
	  $skuLog->sku_mark       =  Redis::get($redis_key);
	  $skuLog->save();

	  return $warehouse_code. sprintf("%02x", $category_id).sprintf("%05x", $spec_id). sprintf("%04x", Redis::Incr($redis_key));
	}  
}
