<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NLAddressController extends Controller
{
    const DEFAULT_URL = 'https://api.postcode.nl/rest';
    const VERSION = '1.1.2.0';
    const CONNECTTIMEOUT = 3;
    const TIMEOUT = 10;

    protected $_restApiUrl = self::DEFAULT_URL;
    //这里的配置等上正式服的时候写到env文件里面去 dev和test服要配金克斯没权限得找人帮忙
    protected $_appKey = 'fpl3QDUsB6thkyGsHoNL3pPG9YhSa5dJ1OoijIHtK6v';//env("NL_KEY")
    protected $_appSecret = '8hgwyMWo5rL44bGXWwTL22O5RecQCVcU22EGBpwibDHyTkYGh4';//env("NL_SECRET")
    protected $_debugEnabled = false;
    protected $_debugData = null;
    protected $_lastResponseData = null;

    public function show(Request $request)
    {
        $this->validate($request, [
            'postcode' => 'required|string|max:7',
            'door_no' => 'required|string|max:10',
        ]);
        $postcode = trim($request->postcode);
        $door_no = trim($request->door_no);
        $postcode = str_replace(' ', '', trim($postcode));
        $houseNumber = trim($door_no);
        $houseNumberAddition = '';
        if ($houseNumberAddition == '') {
            list($houseNumber, $houseNumberAddition) = $this->splitHouseNumber($houseNumber);
        }
        $postcode = strtoupper($postcode);
        $url = $this->_restApiUrl . '/addresses/' . rawurlencode($postcode) . '/' . rawurlencode($houseNumber) . '/' . rawurlencode($houseNumberAddition);
        $response = $this->_doRestCall('GET', $url);
        if ($response['statusCode'] == 200) {
            return formatRet(0, '成功', $response['data']);
        }
        if ($response['statusCode'] == 404) {
            return formatRet(404, '邮编和门牌号码不正确，请仔细检查输入或联系客服', $response['data'], 404);
        }
        return formatRet(500, '无法获取地址信息，请稍后再尝试', [], 500);
    }

    private function splitHouseNumber($houseNumber)
    {
        $houseNumberAddition = '';
        if (preg_match('~^(?<number>[0-9]+)(?:[^0-9a-zA-Z]+(?<addition1>[0-9a-zA-Z ]+)|(?<addition2>[a-zA-Z](?:[0-9a-zA-Z ]*)))?$~', $houseNumber, $match)) {
            $houseNumber = $match['number'];
            $houseNumberAddition = isset($match['addition2']) ? $match['addition2'] : (isset($match['addition1']) ? $match['addition1'] : '');
        }

        return array($houseNumber, $houseNumberAddition);
    }

    protected function _doRestCall($method, $url, array $data = array())
    {
        // Connect using cURL
        $ch = curl_init();
        // Set the HTTP request type, GET / POST most likely.
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        // Set URL to connect to
        curl_setopt($ch, CURLOPT_URL, $url);
        // We want the response returned to us.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Maximum number of seconds allowed to set up the connection.
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTTIMEOUT);
        // Maximum number of seconds allowed to receive the response.
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        // How do we authenticate ourselves? Using HTTP BASIC authentication (http://en.wikipedia.org/wiki/Basic_access_authentication)
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // Set our key as 'username' and our secret as 'password'
        curl_setopt($ch, CURLOPT_USERPWD, $this->_appKey . ':' . $this->_appSecret);
        // To be tidy, we identify ourselves with a User Agent. (not required)
        curl_setopt($ch, CURLOPT_USERAGENT, 'PostcodeNl_Api_RestClient/' . self::VERSION . ' PHP/' . phpversion());
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 跳过证书检查

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在

        // Add any data as JSON encoded information
        if ($method != 'GET' && isset($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        }

        // Various debug options
        if ($this->_debugEnabled) {
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
        }

        // Do the request
        $response = curl_exec($ch);
        // Remember the HTTP status code we receive
        $responseStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseStatusCodeClass = floor($responseStatusCode / 100) * 100;
        // Any errors? Remember them now.
        $curlError = curl_error($ch);
        $curlErrorNr = curl_errno($ch);

        if ($this->_debugEnabled) {
            $this->_debugData['request'] = curl_getinfo($ch, CURLINFO_HEADER_OUT);

            if ($method != 'GET' && isset($data))
                $this->_debugData['request'] .= json_encode($data);

            $this->_debugData['response'] = $response;

            // Strip off header that was added for debug purposes.
            $response = substr($response, strpos($response, "\r\n\r\n") + 4);
        }

        // And close the cURL handle
        curl_close($ch);

        if ($curlError) {
            // We could not connect, cURL has the reason. (we hope)
            //   throw new PostcodeNl_Api_RestClient_ClientException('Connection error `'. $curlErrorNr .'`: `'. $curlError .'`', $curlErrorNr);
        }

        // Parse the response as JSON, will be null if not parsable JSON.
        $jsonResponse = json_decode($response, true);

        $this->_lastResponseData = $jsonResponse;

        return array(
            'statusCode' => $responseStatusCode,
            'statusCodeClass' => $responseStatusCodeClass,
            'data' => $jsonResponse,
        );
    }
}
