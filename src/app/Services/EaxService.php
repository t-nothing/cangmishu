<?php
namespace App\Services;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\TransferException;
use App\Exceptions\BusinessException;

class EaxService
{
    protected $client;

    /**
     * 打印数据接口
     */
    public function getLabelData($eax_number)
    {
        $r = $this->makeRequestToOMS('POST', '/api/v2/eax_order/get_print_label_datas', [
            'headers' => ['ACCESS-TOKEN' => $this->token()],
            'form_params' => [
                'order_numbers' => $eax_number,
            ],
        ]);

        if (! $r['status']) {
            throw new BusinessException(isset($r['tips']) ? $r['tips']: '向 EAX-OMS 系统请求打印数据失败了');
        }

        return $r['data'][0];
    }

    /**
     * 登入接口
     */
    protected function login()
    {
        $r = $this->makeRequestToOMS('POST', '/api/v2/eax_user/login', [
            'form_params' => [
                'appkey' => env('EAXOMS_APPKEY'),
                'secret' => env('EAXOMS_SECRET'),
            ],
        ]);

        if ($r['status']) {
            return $r['token'];
        }

        return '';
    }

    protected function token()
    {
        if ($token = trim(app('cache')->get('oms_token'))) {
            return $token;
        }

        $token = $this->login();

        app('cache')->forever('oms_token', $token);

        return $token;
    }

    protected function makeRequestToOMS($method, $uri, $options)
    {
        info('OMS - 请求开始', compact('method', 'uri', 'options'));

        if (is_null($this->client)) {
            $this->client = new HttpClient([
                'base_uri' => env('EAXOMS_API_URL'),
                'timeout'  => 30.0,
                'verify'   => false,
            ]);
        }

        try {
            $response = $this->client->request($method, $uri, $options);
        } catch(TransferException $e) {
            info('OMS - 请求失败', ['TransferException Message' => $e->getMessage()]);
            return false;
        }

        $body = json_decode((string) $response->getBody(), TRUE);

        info('OMS - 请求结束', compact('body'));

        return $body;
    }
}
