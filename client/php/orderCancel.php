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

    $url = "https://dev-api.cangmishu.com/open/api/order/cancel";
    $app_key = "K005";
    $app_secret = '$2y$10$QOaZ8TslnMUxoRIafF5QvOMnGYOFNi7Z7eWEkxAAX647kYGZys9Mq';

    $params = array(
        'app_key'               =>  $app_key,
        'timestamp'             =>  time()+20 //过期时间
    );

    

    $query = [
        'out_sn'              => 'AAA',
    ];

    $params = array_merge($params, $query);

    $params['sign'] = $makeSign($params, $app_secret);
    try
    {
        $client = new GuzzleHttp\Client(['verify' => false]);
        $res = $client->request('get', $url, 
            [ 
                "query" => $params
            ]
        );

        echo $res->getBody();
    }
    catch(GuzzleHttp\Exception\ClientException $ex) 
    {
        print_r($ex->getMessage());
    }
    
    echo "\n";
