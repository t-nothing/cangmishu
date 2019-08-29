<?php
    require("./vendor/autoload.php");

    /**
     * 申请签名
     */
    $makeSign = function(array $query, $secret)
    {
        ksort($query);
        unset($query['sign']); //这个sign不需要
        $orig = $secret . http_build_query($query);
        return md5($orig);
    };

    $url = "https://dev-api.cangmishu.com/open/api/order";
    $app_key = "K005";
    $app_secret = '$2y$10$QOaZ8TslnMUxoRIafF5QvOMnGYOFNi7Z7eWEkxAAX647kYGZys9Mq';

    $params = array(
        'app_key'               =>  $app_key,
        'timestamp'             =>  time()+20 //过期时间
    );

    $items[] = [
        'sku'               =>  '15ML',
        'qty'               =>  1,
        'sale_price'        =>  1,
        'sale_currency'     =>  'CNY',
    ];


    $query = [
        'out_sn'              => 'AAA',
        'source'                => 'API 来源',
        'sender_fullname'       => '张三',
        'sender_phone'          => '1558888888',
        'sender_country'        => '中国',
        'sender_province'       => '湖南省',
        'sender_city'           => '长沙市',
        'sender_district'       => '岳麓区',
        'sender_address'        => '麓谷企业广场C3栋808',
        'sender_postcode'       => '410000',
        'receiver_fullname'     => '李四',
        'receiver_phone'        => '1556666666',
        'receiver_country'      => '中国',
        'receiver_province'     => '上海',
        'receiver_city'         => '上海市',
        'receiver_district'     => '普陀区',
        'receiver_address'      => '金沙江路1325号',
        'receiver_postcode'     => '200000',
        'items'                 => $items,
    ];

    $params = array_merge($params, $query);

    $params['sign'] = $makeSign($params, $app_secret);
    try
    {
        $client = new GuzzleHttp\Client(['verify' => false]);
        $res = $client->request('POST', $url, 
            [ 
                "form_params" => $params
            ]
        );

        echo $res->getBody();
    }
    catch(GuzzleHttp\Exception\ClientException $ex) 
    {
        print_r($ex->getMessage());
    }
    
    echo "\n";
