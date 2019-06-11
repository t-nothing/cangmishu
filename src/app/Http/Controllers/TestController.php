<?php

namespace App\Http\Controllers;


use App\Http\Requests\BaseRequests;
use GuzzleHttp\Client;

class TestController extends  Controller
{
    protected  $url = "https://pay.nle-tech.com";
    protected  $secret ="dbb358e6d524e45a1ca39fe21b891a37";

    PUBLIC function test(BaseRequests $requests){
        app('log')->info('同步跳转',$requests->all());
        return [
            'status'=>'ok',
            'message' =>"success"
        ];
    }

    public  function pay( ){
        $key = "erppay";
        $secret = $this->secret;
        $url = $this->url;
        $endpoint = "/payments";

        $expire = time() + 5;

        $query = [];
        $query["expire"] = $expire;
        $query["key"] = $key;
        $query["pay_type"] = "0";
        $query["pay_method"] = "2";
        $query["pay_client"] = "0";
        $query["mid"] = "info@yabandmedia.com";
        $query["order_id"] = "ASSSSSSSSSS";
        $query["amount"] = "0.1";
        $query["currency"] = "EUR";
        $query["description"] = "test order";
        $query["redirect_url"] = "http://192.168.0.184:10080/test/redirect";
        $query["notify_url"] = "http://192.168.0.184:10080/test/notifyPay";
        $query["nonce_string"] = "test payment";
        $query["timestamp"] = time() * 1000;

//        $query =
//        [
//            "key" => "erppay",
//            "mid" => "info@yabandmedia.com",
//            "expire" => "1559125481",
//            "timestamp" => "1559125381",
//            "pay_type" => "1",
//            "pay_client" => "0",
//            "pay_method" => "1",
//            "amount" => "0.01",
//            "currency" => "EUR",
//            "notify_url" =>"",
//            "description" =>"",
//            "order_id" =>"",
//        ];

        ksort($query);
        $kvpair = [];
        foreach ($query as $k => $v) {
            $kvpair[] = "{$k}=" . urlencode($v);
        }
        $orig = $secret . implode("&", $kvpair);
        $query["sign"] = md5($orig);
//        dd($query);
        $client = new Client();
        $res = $client->request('POST', $url . $endpoint,
            [
                "form_params" => $query,
            ]
        );
        $re =  trim($res->getBody()->getContents());
        $re = json_decode($re,true);
        dd($re);
    }

    public function query()
    {


        $key = "erppay";
        $secret = $this->secret;
        $url = $this->url;
        $endpoint = "/orderquery";

        $expire = time() + 5;

        $query = [];
        $query["expire"] = $expire;
        $query["key"] = $key;
        $query["pay_type"] = "0";
        $query["pay_method"] = "2";
        $query["pay_client"] = "0";
        $query["mid"] = "info@yabandmedia.com";
        $query["trade_id"] = "88a1f139-5c38-aad2-6bc6-1aa552c6318d";
        $query["order_id"] = "AS1231";
        $query["timestamp"] = time() * 1000;
        ksort($query);
        $kvpair = [];
        foreach ($query as $k => $v) {
            $kvpair[] = "{$k}=" . urlencode($v);
        }
        $orig = $secret . implode("&", $kvpair);
        $query["sign"] = md5($orig);
//        dd($query);
        $client = new Client();
        $res = $client->request('POST', $url . $endpoint,
            [
                "form_params" => $query,
            ]
        );

        $re =  trim($res->getBody()->getContents());
        $re = json_decode($re,true);

        dd($re);
    }

    public function refund()
    {
        $key = "erppay";
        $secret = $this->secret;
        $url = $this->url;
        $endpoint = "/refund";

        $expire = time() + 5;

        $query = [];
        $query["expire"] = $expire;
        $query["key"] = $key;
        $query["pay_type"] = "0";
        $query["pay_method"] = "2";
        $query["pay_client"] = "0";
        $query["mid"] = "info@yabandmedia.com";
        $query["trade_id"] = "4708e19d-ae7c-13b3-dca1-2bc49edee0b9";
        $query["order_id"] = "AS1231";
        $query["timestamp"] = time() * 1000;
        $query["amount"] = "0.1";
        $query["currency"] = "EUR";
        $query["refund_reason"] = "test";
        $query["notify_url"] = "http://192.168.0.184:10080/yaband/notify/refund";

        ksort($query);
        $kvpair = [];
        foreach ($query as $k => $v) {
            $kvpair[] = "{$k}=" . urlencode($v);
        }
        $orig = $secret . implode("&", $kvpair);
        $query["sign"] = md5($orig);
//        dd($query);
        $client = new Client();
        $res = $client->request('POST', $url . $endpoint,
            [
                "form_params" => $query,
            ]
        );

        $re =  trim($res->getBody()->getContents());
        $re = json_decode($re,true);

        dd($re);

    }

    public function cancel()
    {
        $key = "testpay";
        $secret = "2fe541634e66ccc0e4f8e1b0dbc45bec";
        $url = "http://192.168.0.184:1323";
        $endpoint = "/cancel";

        $expire = time() + 5;

        $query = [];
        $query["expire"] = $expire;
        $query["key"] = $key;
        $query["pay_type"] = "0";
        $query["pay_method"] = "2";
        $query["pay_client"] = "0";
        $query["mid"] = "info@yabandmedia.com";
        $query["trade_id"] = "40a0fb23-39a3-fc2e-96d9-0e05a116e454";
        $query["timestamp"] = time() * 1000;

        ksort($query);
        $kvpair = [];
        foreach ($query as $k => $v) {
            $kvpair[] = "{$k}=" . urlencode($v);
        }
        $orig = $secret . implode("&", $kvpair);
        $query["sign"] = md5($orig);
        $client = new Client();
        $res = $client->request('POST', $url . $endpoint,
            [
                "form_params" => $query,
            ]
        );

        $re =  trim($res->getBody()->getContents());
//        $re = json_decode($re,true);

        dd($re);

    }

    public function   YaBandPayNotify()
    {


        $secret = "62184c09df1aeb63239e07079875be81";
        $url = "http://192.168.0.184:1323";
        $endpoint = "/notify/yaband/redirect";

//        $params = [
//            "type"=> "payment",
//            "user"=> "info@yabandmedia.com",
//            "order_id"=> "AS12311",
//            "trade_id"=> "c6db6ab0-a29d-9fac-f964-7b9953087503",
//            "transaction_id"=> "4200000298201905227377147799",
//            "amount"=> "0.1",
//            "currency"=> "EUR",
//            "settlement_amount"=> "0.1",
//            "settlement_currency"=> "EUR",
//            "exchange_rate"=> "1",
//            "description"=> "yaband test",
//            "createDate"=> "1558510731",
//            "state"=> "paid",
//            "demo"=> "http://baidu.com"
//        ];
//        $params = [
//            "type"=> "erppay",
//            "user"=> "info@yabandmedia.com",
//            "order_id"=> "1523750501751",
//            "trade_id"=> "996b9ad6-a20b-d82b-b40b-d24631c821d8",
//            "transaction_id"=> "4200000298201905227377147799",
//            "amount"=> "0.1",
//            "currency"=> "EUR",
//            "settlement_amount"=> "0.1",
//            "settlement_currency"=> "EUR",
//            "exchange_rate"=> "1",
//            "description"=> "ERP 在线充值, 留言: :(90002) 这是一笔0.1欧元测试支付",
//            "createDate"=> "1558510731",
//            "state"=> "paid",
//            "demo"=> "ERP 在线充值, 留言: :(90002) 这是一笔0.1欧元测试支付"
//        ];

//        $arr = [
//            "key" => "erppay",
//            "mid" => "info@yabandmedia.com",
//            "expire" => "1559269414",
//            "timestamp" => "1559269314",
//            "pay_type" => "0",
//            "pay_client" => "0",
//            "pay_method" => "0",
//            "amount" => "0.10",
//            "currency" => "EUR",
//            "notify_url" => "https://dev-schafera-erp-api.nle-tech.com/api/v2/vip_recharge/callback/EutechneYaBandPay",
//            "redirect_url" => "https://dev-schafera-erp-api.nle-tech.com/api/v2/vip_recharge/redirect/EutechneYaBandPay",
//            "description" => "ERP 在线充值, 留言: :(90002) 这是一笔0.1欧元测试支付",
//            "order_id" => "1523750445802",
//            "sign" => "cd2428e9fb74eca860db5fdaa9aa8493",
//];

            $ss='{"sign": "d2015d551231d24448fe219d7eeb797a","order_id": "1523750505788","trade_id":"a12c8b9d-d3d1-3c1c-65b7-2e515d1fa8db","description": "ERP(alipay) :(90001) test","amount": "0.10","currency": "EUR","state": "paid","created_date": "1559356333","exchange_rate": "1","settlement_amount": "0.10","settlement_currency": "EUR"}';

//            $params=json_decode($ss,true);
//            unset($params['sign']);

                $params = [
                    "type"=> "payment",
                    "user"=> "info@yabandmedia.com",
                    "order_id"=> "1523750505788",
                    "trade_id"=> "55884d9c-d587-8041-b2d5-f375760f06e1",
                    "transaction_id"=> "4200000298201905227377147799",
                    "amount"=> "0.10",
                    "currency"=> "EUR",
                    "settlement_amount"=> "0.10",
                    "settlement_currency"=> "EUR",
                    "exchange_rate"=> "1",
                    "description"=> "ERP(alipay) :(90001) test",
                    "createDate"=> "1559356333",
                    "state"=> "paid",
                    "demo"=> "pay"
                ];



        ksort($params);
        $kvpair = [];
        foreach ($params as $k => $v) {
            $kvpair[] = "{$k}=" . $v;
        }
        $orig = implode("&", $kvpair);

//        dd($orig,$secret,hash_hmac('sha256',$orig, $secret));


        $query['sign'] = hash_hmac('sha256',$orig, $secret);
        $query['data'] = $params;
//        dd($query);
        dd(json_encode($query));
        $client = new Client();
       $query = json_encode($query);
//        dd($query);
        $res = $client->request('POST', $url . $endpoint,
            [
//                "form_params" => $query,
                "json" => $query,
            ]



        );

        $re =  trim($res->getBody()->getContents());
        $re = json_decode($re,true);
        dd($re);
    }

    public function   YaBandRefundNotify()
    {

        $key = "testpay";
        $secret = "62184c09df1aeb63239e07079875be81";
        $url = "http://192.168.0.184:1323";
        $endpoint = "/notify/yaband/refund";
        $params = [
            "type"=> "payment",
            "user"=> "info@yabandmedia.com",
            "order_id"=> "123456",
            "trade_id"=>  "14cc12bc-76cb-5636-968d-5dc2bb1d8012",
            "refund_id" => "fe3936be-ddcb-42fd-ba5c-2582de637d34",
            "refund_amount"=> "0.10",
		    "refund_currency"=> "EUR",
            "description"=> "yaband test",
            "createDate"=> "1558510731",
            "state"=> "refund processing",
        ];

        ksort($params);
        $kvpair = [];
        foreach ($params as $k => $v) {
            $kvpair[] = "{$k}=" .$v;
        }
        $orig =implode("&", $kvpair);
        $query['sign'] =hash_hmac('sha256',$orig, $secret);
        $query['data'] = $params;

        $ss = "createDate=1558510731&description=yaband test&order_id=123456&refund_amount=0.10&refund_currency=EUR&refund_id=fe3936be-ddcb-42fd-ba5c-2582de637d34&state=refund processing&trade_id=14cc12bc-76cb-5636-968d-5dc2bb1d8012&type=payment&user=info@yabandmedia.com";

        $token = "62184c09df1aeb63239e07079875be81";
//        dd($secret == $token);
//        dd($orig,$secret,$query['sign']);
        $client = new Client();
//       $query = json_encode($query);
//        dd($query);
        $res = $client->request('POST', $url . $endpoint,
            [
//                "form_params" => $query,
                "json" => $query,
            ]

        );
        $re =  trim($res->getBody()->getContents());
        $re = json_decode($re,true);

        dd($re);
    }

    public  function  notifyPay(BaseRequests $requests)
    {
        app('log')->info('异步回调通知',$requests->all());
        return [
            'status'=>'ok',
            'message' =>'success'
        ];
    }

}