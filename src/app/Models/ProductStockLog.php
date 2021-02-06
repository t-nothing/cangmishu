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

    public function spec()
    {
        return $this->belongsTo('App\Models\ProductSpec', 'spec_id', 'id');
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
    		self::TYPE_IN   => 'message.statusBatchIn',
			self::TYPE_SHELF   => 'message.statusShelf',
			self::TYPE_PICKING => 'message.statusPick',
			self::TYPE_OUTPUT  => 'message.statusOutbound',
			self::TYPE_COUNT   => 'message.statusRecount',
			// self::TYPE_OFFLINE => '作废',
    	];

        return isset($names[$this->type_id]) ? trans($names[$this->type_id]) : '';
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
                self::TYPE_IN   => 'message.statusBatchIn',
                self::TYPE_SHELF   => 'message.statusShelf',
                // self::TYPE_PICKING => 'message.statusPick',
                self::TYPE_OUTPUT  => 'message.statusOutbound',
                self::TYPE_COUNT   => 'message.statusRecount',
                // self::TYPE_OFFLINE => '作废',
            ];;
        $result = [];
        foreach ($arr as $key => $value) {
            $result[] = [
                'id'        =>$key,
                'name'      =>trans($value),
            ];
        }

        return $result;
    }
}