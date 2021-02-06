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

class BatchLog extends Model
{
    protected $table = 'batch_log';

//    protected $validate = [
//        'type_id' => 'required|integer|min:1',
//        'relevance_code' => 'required|string|max:255',
//        'ean' => 'string|max:255',
//        'num' => 'required|integer|min:1',
//        'balance_num' => 'required|integer|min:1',
//        'operator' => 'required|integer|min:1',
//        'warehouse_id' => 'required|integer|min:1',
//        'owner_id' => 'required|integer|min:1',
//        'order_sn' => 'string|max:255',
//    ];
//
    public function AddLog($data)
    {
        $add = [
            'batch_id'  =>$data['batch_id'],
            'stock_id'  => $data['stock_id'],
            'num'       => $data['num'],
            'operator'  => $data['operator'],
            'warehouse_id'=>$data['warehouse_id'],
            'owner_id'  =>$data['owner_id'],
        ];
       // echo json_encode($data);exit;
        return $this->insertGetId($data);
    }
}
