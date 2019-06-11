<?php

namespace App\Http\Controllers;


use GuzzleHttp\Client;

class WPController extends  Controller
{

    public  function pay( ){
        $key = "testpay";
        $secret = "2fe541634e66ccc0e4f8e1b0dbc45bec";
        $url = "http://192.168.0.184:1323";
        $endpoint = "/payments";

        $expire = time() + 5;

        $query = [];
        $query["expire"] = $expire;
        $query["key"] = $key;
        $query["pay_type"] = "1";  // 1代表twopaynow
        $query["pay_method"] = "1"; //0 代表支付宝，1代表微信
        $query["pay_client"] = "0"; //0 代表web，app 1代表微信公众号
        $query["mid"] = "421500002";
        $query["order_id"] = "A123456";
        $query["amount"] = "0.1";
        $query["currency"] = "EUR";
        $query["description"] = "test order";
        $query["redirect_url"] = "https://dev-api.cangmishu.com/";
        $query["notify_url"] = "https://dev-api.cangmishu.com/";
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

    public function query()
    {
        $key = "testpay";
        $secret = "2fe541634e66ccc0e4f8e1b0dbc45bec";
        $url = "http://192.168.0.184:1323";
        $endpoint = "/orderquery";

        $expire = time() + 5;

        $query = [];
        $query["expire"] = $expire;
        $query["key"] = $key;
        $query["pay_type"] = "1";  // 1代表twopaynow
        $query["pay_method"] = "1"; //0 代表支付宝，1代表微信
        $query["pay_client"] = "0"; //0 代表web，app 1代表微信公众号
        $query["mid"] = "421500002";
        $query["trade_id"] = "W19052718013022544";
        $query["order_id"] = "A123456";
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
        $re = json_decode($re,true);

        dd($re);
    }

    public function refund()
    {
        $key = "testpay";
        $secret = "2fe541634e66ccc0e4f8e1b0dbc45bec";
        $url = "http://192.168.0.184:1323";
        $endpoint = "/refund";

        $expire = time() + 5;

        $query = [];
        $query["expire"] = $expire;
        $query["key"] = $key;
        $query["pay_type"] = "1";
        $query["pay_method"] = "1";
        $query["pay_client"] = "0";
        $query["mid"] = "421500002";
        $query["trade_id"] = "W19052718101790010";
        $query["order_id"] = "A123456";
        $query["timestamp"] = time() * 1000;
        $query["amount"] = "0.1";
        $query["currency"] = "EUR";
        $query["refund_reason"] = "test";
        $query["notify_url"] = "https://dev-api.cangmishu.com/";

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
        $query["pay_type"] = "1";
        $query["pay_method"] = "1";
        $query["pay_client"] = "0";
        $query["mid"] = "421500002";
        $query["trade_id"] = "W19052718013022544";
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
        $re = json_decode($re,true);

        dd($re);

    }

    public function   Notify()
    {

        $key = "testpay";
        $secret = "testpay";
        $url = "http://192.168.0.184:1323";
        $endpoint = "/notify/twopaynow/qr";
        $params = [
            "function" => "precreate",
            "mid" => "421500002",
            "timestamp" => "1487849674744",
            "merchant_trade_no" => "W19052718101790010",
            "trade_status" => "TRADE_SUCCESS",
            "amount" => "0.1",
            "currency" => "EUR",
            "passback_parameters" => "",
        ];

        ksort($params);
        $kvpair = [];
        foreach ($params as $k => $v) {
            $kvpair[] = "{$k}=" . urlencode($v);
        }
        $orig = $secret . implode("&", $kvpair);
        $params['sign'] = md5($orig);
        $client = new Client();
//       $query = json_encode($query);
//        dd($query);
        $res = $client->request('GET', $url . $endpoint,
            [
//                "form_params" => $query,
//                "json" => $query,
                'query' => $params,
            ]



        );

        $re =  trim($res->getBody()->getContents());
        $re = json_decode($re,true);

        dd($re);
    }

    public  function  notifyPay()
    {
        return [
            'status'=>'ok',
//            'key' =>'info@yabandmedia.com',
            'message' =>'success'
        ];
    }

}