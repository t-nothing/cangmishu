<?php

namespace App\Models;

class ProductStockLog extends Model
{
    const TYPE_BATCH    = 1;// 入库
    const TYPE_IN    = self::TYPE_BATCH;// 入库
    const TYPE_SHELF    = 2;// 上架
    const TYPE_PUTON    = self::TYPE_SHELF;// 上架
    const TYPE_PICKING  = 3;// 拣货
    const TYPE_OUTPUT   = 4;// 出库
    const TYPE_COUNT    = 5;// 盘点
    const TYPE_OFFLINE  = 6;// 下架（作废）
    const TYPE_MOVE     = 7;// 移动货位

	// const TYPE_BATCH_SHELF    = 1;// 入库上架
 //    const TYPE_OUTPUT   = 2;// 出库
 //    const TYPE_COUNT    = 3;// 盘点
//    const TYPE_OFFLINE  = 6;// 下架（作废）


    protected $table = 'product_stock_log';

    protected $guarded = [];

    protected $appends = [
    	'type',
    ];

    public function operatorUser()
    {
        return $this->belongsTo('App\Models\User', 'operator', 'id')->withDefault([
            'nickname' => '',
        ]);
    }



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
    		self::TYPE_IN   => '入库',
			self::TYPE_SHELF   => '上架',
			self::TYPE_PICKING => '拣货',
			self::TYPE_OUTPUT  => '出库',
			self::TYPE_COUNT   => '盘点',
			// self::TYPE_OFFLINE => '作废',
    	];

        return isset($names[$this->type_id]) ? $names[$this->type_id] : '';
    }

    /**
     * @return array
     */
    public function ScopeOfWarehouse($query, $warehouse_id)
    {
        return $query->where('warehouse_id', $warehouse_id);
    }

    public static function getAllType(){
        $arr =  [
                self::TYPE_IN   => '入库',
                self::TYPE_SHELF   => '上架',
                // self::TYPE_PICKING => '拣货',
                self::TYPE_OUTPUT  => '出库',
                self::TYPE_COUNT   => '盘点',
                // self::TYPE_OFFLINE => '作废',
            ];;
        $result = [];
        foreach ($arr as $key => $value) {
            $result[] = [
                'id'        =>$key,
                'name'      =>$value,
            ];
        }

        return $result;
    }
}