<?php
namespace App\Services;

use App\Concerns\CommentApiAuth;

class MallService
{
    use CommentApiAuth;

    public function makeReuqestToMall($path, $params)
    {
        $data = $this->makeReuqest(env('MES_API_URL'), $path, $params, env('MES_API_KEY'), env('MES_API_ID'));
        return $data;
    }

    /**
     * 仓库自提确认收货通知商城
     * @param $params
     * @return mixed
     */
    public function orderOneselfOver($params)
    {
        return $this->makeReuqestToMall('/api/order/orderOneselfOver', $params);
    }

    /**
     * 通知商城订单在拣货了
     */
    public function notifyPicking($out_sn)
    {
        return $this->makeReuqestToMall('/api/order/wmsPush', ['type' => 1, 'out_sn' => $out_sn]);
    }

    /**
     * 通知商城订单在打包了
     */
    public function notifyPacking($out_sn)
    {
        return $this->makeReuqestToMall('/api/order/wmsPush', ['type' => 2, 'out_sn' => $out_sn]);
    }

    // ---------------------------------------------------------------------------

    
    //@author lym
    //获取商城出库清单
    public function printPicking($invoice_number)
    {
        $params = [
            'invoice_number' => $invoice_number
        ];
        return $this->makeReuqestToMall('/api/order/printPicking', $params);
    }
    
    //@author lym
    //获取蔬菜捡货单
    public function printGreensPicking($time, $type_id)
    {
        $params = [
            'time' => $time,
            'type_id' => $type_id
        ];
        return $this->makeReuqestToMall('/api/order/printGreensPicking', $params);
    }
}
