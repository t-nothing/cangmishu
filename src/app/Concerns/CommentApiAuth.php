<?php

namespace App\Concerns;
use GuzzleHttp\Client;

trait CommentApiAuth
{
    private function genSign($url, $params, $key)
    {
        $mk = self::makeSource($url, $params);
        $my_sign = hash_hmac("sha256", $mk, strtr($key, '-_', '+/'), true);
        $my_sign = base64_encode($my_sign);
        return $my_sign;
    }

    private function makeSource($url, $params)
    {
        $strs = rawurlencode($url) . '&';

        ksort($params);
        $query_string = array();
        foreach ($params as $key => $val) {
            // 这个GuzzleHttp比较奇葩，值为空就不会添加到请求中，所以这里需要处理下
            if (!isset($val) && empty($val)) {
                continue;
            }
            array_push($query_string, $key . '=' . $val);
        }
        $query_string = join('&', $query_string);

        return $strs . str_replace('~', '%7E', rawurlencode($query_string));
    }

    public function makeReuqest($url, $apiName, $params, $key, $key_id)
    {
        $http = new Client(['base_uri' => $url, 'verify' => false]);

        $params['stamp'] = time();
        $params['key_id'] = $key_id;
        $sign = self::genSign($apiName, $params, $key);
        $params['sign'] = $sign;
        $response = $http->post($apiName, ['form_params' => $params]);
        $bodyData = $response->getBody();
        $responseData = json_decode((string)$bodyData, true);

        info('发起一个新请求', compact('url', 'apiName', 'params', 'responseData'));

        return $responseData;
    }
}
