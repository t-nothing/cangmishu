<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use App\Exceptions\BusinessException;

class TrackingService
{
    /**
     * 通知 - 在拣货了
     */
    public function notifyPicking($order, $history)
    {
        return $this->pushExpressInfo([
            'freight_name'    => $order->express_code,
            'freight_code'    => $order->express_code,
            'outer_key'       => $order->express_num,
            'express_number'  => $order->express_num,
            'status'          => 10000,
            'outer_status_id' => $history->status,
            'outer_time'      => $history->created_at->timestamp,
        ] , '仓库开始捡货');
    }

    /**
     * 通知 - 在打包了
     */
    public function notifyPacking($order, $history)
    {
        return $this->pushExpressInfo([
            'freight_name'    => $order->express_code,
            'freight_code'    => $order->express_code,
            'outer_key'       => $order->express_num,
            'express_number'  => $order->express_num,
            'status'          => 10000,
            'outer_status_id' => $history->status,
            'outer_time'      => $history->created_at->timestamp,
        ] , '包裹捡货完毕，打包完毕');
    }

    /**
     * 推送物流信息
     *
     * @param $expressInfo
     * @param $context
     */
    public function pushExpressInfo($expressInfo, $context)
    {
        $url = '/newapi/pull/addLogistics';
        $data = [
            'express_company_name' => $expressInfo['freight_name'],
            'express_company_num'  => $expressInfo['freight_code'],
            'outer_key'            => $expressInfo['express_number'],
            'express_number'       => $expressInfo['express_number'],
            'context'              => $context,
            'context_en'           => 'English',
            'status'               => $expressInfo['status'],
            'outer_status_id'      => $expressInfo['outer_status_id'],
            'outer_time'           => $expressInfo['outer_time'],
            'is_open'              => 1,
            'is_invalid'           => 0
        ];

        $this->newMakeReuqest($url, $data);
    }

    private function newGenSign($tokenParams)
    {
        $secret=env('EXPRESS_SECRET');
        ksort($tokenParams);
        $_sign = $secret.'&'.http_build_query($tokenParams);
        $sign = md5($_sign);
        return $sign;
    }

    private function newMakeReuqest($apiName, $params)
    {
        $params['app_key'] = env('EXPRESS_APPKEY');
        $params['timestamp'] = Carbon::now()->timestamp;
        $params['sign'] = $this->newGenSign($params);
        $token = http_build_query($params);

        if (env('EXPRESS_API_URL') == '') {
            return true;
        }

        $url = env('EXPRESS_API_URL').$apiName;

        app('log')->info('向物流系统发起新请求', compact('url', 'token', 'params'));

        $http = new Client(['headers' => ['Authorization' => $token], 'verify' => false]);
        $response = $http->post($url, ['form_params' => $params]);
        $bodyData = $response->getBody();
        app('log')->info('接收返回参数' . $bodyData);
        $responseData = json_decode((string) $bodyData, true);
        return $responseData;
    }

    // ---------------------------------------------------------------------------------
    // 物流系统一期
    // ---------------------------------------------------------------------------------

    private function genSign($url,$params){
        $mk = self::makeSource($url, $params);
        $my_sign = hash_hmac("sha256", $mk, strtr(env('EXPRESS_KEY'), '-_', '+/'),true);
        $my_sign = base64_encode($my_sign);
        return $my_sign;
    }

    private function makeSource($url,$params){
        $strs = rawurlencode($url) . '&';

        ksort($params);
        $query_string = array();
        foreach ($params as $key => $val ) {
            // 这个GuzzleHttp比较奇葩，值为空就不会添加到请求中，所以这里需要处理下
            if (empty($val)) {
                continue;
            }
            array_push($query_string, $key . '=' . $val);
        }
        $query_string = join('&', $query_string);

        return $strs . str_replace('~', '%7E', rawurlencode($query_string));
    }

    private function makeReuqest($apiName,$params){
        $http=new Client(['verify'=>false]);
        $url=env('EXPRESS_URL').$apiName;

        $params['stamp'] = time();
        $sign = $this->genSign($apiName,$params);
        $params['sign'] = $sign;
        $response = $http->post($url,['form_params' => $params]);
        $bodyData = $response->getBody();
        $responseData = json_decode((string) $bodyData,true);
        return $responseData;
    }

    /**
     * 查询物流
     * @param $internationalExpressNumber
     * @return mixed
     */
    public function getExpress($internationalExpressNumber){
        $apiName = '/api/NleExpress/demandExpressInfo';
        $data=[
            'internationalExpressNumber'=>$internationalExpressNumber
        ];
        return $this->makeReuqest($apiName,$data);
    }

    /**
     * 推送物流数据
     * @param $apiData
     * @return mixed
     */
    public function sendExpress($apiData){
        $apiName = '/api/NleExpress/inputExpressInfo';
        $data=[
            'internationalExpressNumber'=>$apiData['internationalExpressNumber'],
            'context'                   =>$apiData['context'],
            'time'                      =>$apiData['time']
        ];
        return $this->makeReuqest($apiName,$data);
    }

    public function inputExpress($express_num,$context){
        $apiName = '/api/NleExpress/inputExpress';
        $data=[
            'internationalExpressNumber'    =>  $express_num,
            'internationalExpressName'      =>  $context,
        ];
        return $this->makeReuqest($apiName,$data);
    }
}