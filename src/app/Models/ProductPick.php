<?php
/**
 * Created by PhpStorm.
 * User: xiao
 * Date: 2018/5/2
 * Time: 11:28
 */

namespace App\Models;

use App\Models\Model;

class ProductPick extends Model
{
    protected $table = 'product_pick';
    protected $guarded = [];
    const PICK_STATUS_CREATED = 1;//捡货开始
    const PICK_STATUS_PROCEED = 2;//捡货进行中(暂时不使用)
    const PICK_STATUS_FINISH = 3;//捡货完成
    const PICK_STATUS_CHECKOUT = 4;//验货完成
    const PICK_STATUS_WAIVE = 10;//放弃捡货

    /**
     * 添加数据
     * @param $data
     */
    public function addPick($data)
    {
        $add = [
            'product_stock_id' => $data['product_stock_id'],
            'num' => $data['num'],
            'old_num' => $data['old_num'],
            'new_num' => $data['new_num'],
            'status' => $data['status'],
            'shipment_num' => $data['shipment_num'],
            'created_at' => time(),
        ];
        return $this->create($add);
    }

    /**
     * 修改数据
     * @param $data
     * @return string
     */
    public function updatePick($data)
    {
        $modelObj = $this->getIns();
        if (isset($data['id'])) {
            $modelObj = $modelObj->where('id', $data['id']);
            unset($data['id']);
        } else {
            $modelObj = $modelObj->where('shipment_num', $data['shipment_num'])->where('product_stock_id', $data['product_stock_id']);
            unset($data['product_stock_id']);
            unset($data['shipment_num']);
        }
        return $modelObj->where('status', self::PICK_STATUS_CREATED)->update($data);
    }

    /**
     * 释放检货单
     * @param $shipment_num
     * @return mixed
     */
    public function release($shipment_num='all')
    {
        if($shipment_num=='all'){
            return $this->whereIn('status', [self::PICK_STATUS_CREATED, self::PICK_STATUS_PROCEED])->update(['status' => self::PICK_STATUS_WAIVE]);
        }else{
            return $this->where('shipment_num', $shipment_num)->update(['status' => self::PICK_STATUS_WAIVE]);
        }
    }
}