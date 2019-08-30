<?php

namespace App\Models;

use Illuminate\Support\Facades\Redis;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSpec extends Model
{
    use SoftDeletes;
	protected $table = 'product_spec';
    protected $fillable = ['warehouse_id', 'owner_id', 'name_cn', 'name_en', 'relevance_code', 'product_id','net_weight','gross_weight','is_warning', 'sale_price', 'purchase_price'];

    // public   $appends= ['product_name', 'spec_name', 'stockin_num'];

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
		return $this->belongsTo('App\Models\Product', 'product_id', 'id')->when(!is_null($this->warehouse_id), function($query){
            //加一个仓库ID有索引更快
            $query->where('warehouse_id', $this->warehouse_id);
        });
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
        $lang = app('translator')->locale()?:'cn';
        if($lang == 'zh-CN'){
            $lang='cn';
        }
		if (isset($this->product)) {
			$name = $this->product['name_'.$lang]
				. '('
				. $this->{'name_'.$lang}
				. ')';
		}

		return $name;
	}

    /**
     * @return string
     */
    public function getSpecNameAttribute()
    {
        $lang = app('translator')->locale()?:'cn';
        if($lang == 'zh-CN'){
            $lang='cn';
        }

        return $this->{'name_'.$lang};
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
		$query->ofWarehouse(app('auth')->warehouse()->id)->where('relevance_code', '=', $keywords)->whose(app('auth')->ownerId());

		//跟据货品信息搜索
        $query->orWhereHas('product', function($q) use ($keywords) {
            $q->ofWarehouse(app('auth')->warehouse()->id)
              ->whose(app('auth')->ownerId())
              ->where('name_cn', 'like', '%' . $keywords . '%')
              ->orWhere('name_en', 'like', '%' . $keywords . '%')
              ->orWhere('hs_code', 'like', '%' . $keywords . '%')
              ->orWhere('origin', 'like', '%' . $keywords . '%');
        });

		return $query;
	}

    /**
     * 限制查询属于指定仓库。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfWarehouse($query, $warehouse_id)
    {
        return $query->where('product_spec.warehouse_id', $warehouse_id);
    }

    /**
     * 限制查询属于指定用户。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhose($query, $user_id)
    {
        return $query->where('product_spec.owner_id', $user_id);
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
     * 得到进货价格
     **/
    static function getPurchasePrice($id)
    {
        return Self::find($id)->purchase_price??0;
    }

    /**
     * 得到销售价格
     **/
    static function getSalePrice($id)
    {
        return Self::find($id)->sale_price??0;
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
	  $redis_key      = 'wms_cangmishu_spec'.$spec_id;
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
