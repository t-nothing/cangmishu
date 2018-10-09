<?php

namespace App\Models;

use App\Models\Model;

class ProductStockLog extends Model
{
	const TYPE_BATCH    = 1;// 入库
    const TYPE_SHELF    = 2;// 上架
    const TYPE_PICKING  = 3;// 捡货
    const TYPE_OUTPUT   = 4;// 出库
    const TYPE_COUNT    = 5;// 盘点
    const TYPE_OFFLINE  = 6;// 下架（作废）

    protected $table = 'product_stock_log';

    protected $guarded = [];

    protected $appends = [
    	'type',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function getTypeAttribute()
    {
    	$names = [
    		self::TYPE_BATCH	 => '入库',
			self::TYPE_SHELF	 => '上架',
			self::TYPE_PICKING => '捡货',
			self::TYPE_OUTPUT	 => '出库',
			self::TYPE_COUNT	 => '盘点',
			self::TYPE_OFFLINE => '作废',
    	];

        return isset($names[$this->type_id]) ? $names[$this->type_id] : '';
    }
}