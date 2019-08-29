<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;

class TestThirdPartRequest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSign()
    {


        /**
         * 申请签名
         */
        $makeSign = function(array $query, $secret)
        {

            ksort($query);
            $kvpair = [];
            unset($query['sign']); //这个sign不需要

            foreach ($query as $k => $v) {
                $kvpair[] = "{$k}=" . urlencode($v);
            }
            $orig = $secret . implode("&", $kvpair);

            return md5($orig);
        };

        $app_key = "K005";
        $app_secret = '$2y$10$QOaZ8TslnMUxoRIafF5QvOMnGYOFNi7Z7eWEkxAAX647kYGZys9Mq';

        $params = array(
            'app_key'               =>  $app_key,
            'timestamp'             =>  Carbon::now()->timestamp + 20
        );

        $query = [];

        $params = array_merge($params, $query);

        $params['sign'] = $makeSign($params, $app_secret);

        try{
            $http = new Client(['verify' => false]);
            $response = $http->request('GET', env("APP_URL")."/open/api/stock/sku", ['query' => $params]);
            $bodyData = $response->getBody();
            print_r((string)$bodyData);
            $responseData = json_decode((string) $bodyData, true);
            
            print_r($responseData);
        }catch (\Exception $e){
            print_r($e->getMessage());
            // info('请求物流系统异常', ['TrackingService Message' => $e->getMessage()]);
            // $this->ExceptionInform($e,'请求物流系统异常');
        }
        // $this->assertTrue(true);
    }
}
