<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Concerns;
use GuzzleHttp\Client;
use App\Models\AppAccount;

trait ThirdPartyPush {

    /**
     * 申请签名
     */
    public function makeSign(array $query, $secret)
    {
        ksort($query);
        unset($query['sign']); //这个sign不需要
        $orig = $secret . http_build_query($query);
        return md5($orig);
    }

    public function askPost($query, $warehouseId, $type)
    {
        $apiInfo = AppAccount::where('warehouse_id', $warehouseId)->where('is_enabled_push', 1)->first();
        if(!$apiInfo || strlen($apiInfo['push_url']) <=10)
        {
            app('log')->info('第三方推送未配置');
            return false;
        }

        $push_url = $apiInfo["push_url"];
        $app_key = $apiInfo["app_key"];
        $app_secret = $apiInfo["app_secret"];

        $params = array(
            'app_key'               =>  $app_key,
            'timestamp'             =>  time()+20 //过期时间
        );

        $query['method'] = $type; //stockChange orderShipped orderCancel
        $params = array_merge($params, $query);

        $params['sign'] = $this->makeSign($params, $app_secret);
        try
        {
            app('log')->info('开始向'.$push_url.'推送信息', $params);
            $client = new Client(['verify' => false]);
            $res = $client->request('post', $push_url, 
                [ 
                    "form_params" => $params
                ]
            );

            app('log')->info('第三方推送回调成功结果'.$res->getBody());
        }
        catch(GuzzleHttp\Exception\ClientException $ex) 
        {
            app('log')->info('第三方推送回调失败结果'.$ex->getMessage());
        }
    }

}