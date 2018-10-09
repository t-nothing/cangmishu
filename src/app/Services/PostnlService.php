<?php
/**
 * DESC:wms接口服务
 * Author: YangBin
 * DateTime: 2017/05/02 13:15
 * Email: yangbin@nle-tech.com
 */

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GrahamCampbell\Flysystem\Facades\Flysystem;

define('POSTNL_LABEL_SANDBOX', TRUE);
define('POSTNL_LABEL_CUSTOMER_CODE', 'TZVU');
define('POSTNL_LABEL_CUSTOMER_NUMBER', '10508393');
define('POSTNL_LABEL_TYPE', '3S');
define('POSTNL_LABEL_SERIE', '000000000-999999999');
define('POSTNL_LABEL_API_KEY', '1uWXF3jXhNnbRY0GnAZ3GToxI4hjJFhA');

class PostnlService
{

    protected $Sandbox = false;
    const SENDER_CODE = '02';
    const RECEIVER_CODE = '01';

    /**
     * 产生订单号
     * Service indicator (a2)
     * Serial number(n8)
     * Check digit(n1)
     * Country code(a2)
     */
    public function generateBarcode()
    {
        $url = env("POSTNL_API_URL") . "/shipment/v1_1/barcode";

        $arr['CustomerCode'] = POSTNL_LABEL_CUSTOMER_CODE;
        $arr['CustomerNumber'] = POSTNL_LABEL_CUSTOMER_NUMBER;
        $arr['Type'] = POSTNL_LABEL_TYPE;
        $arr['Serie'] = POSTNL_LABEL_SERIE;

        $url = $url . "?" . http_build_query($arr);

        $client = new Client(['verify' => false]);

        // @TODO 这个地方最好封装下，可以统一处理异常和重试等操作
        $response = $client->request("GET", $url, [
            'headers' => ['apikey' => POSTNL_LABEL_API_KEY],
        ]);

        $res = json_decode($response->getBody(), true);
        return $res['Barcode'];
    }

    public function generateLabel($data)
    {
//        $data['barcode'] = $this->generateBarcode();
//        $data['receiverName'] = 'Test';
//        $data['receiverFirstName'] = '';
//        $data['receiverStreet'] = '';
//        $data['receiverHouseNr'] = '';
//        $data['receiverCity'] = '';
//        $data['receiverZipcode'] = '';
//        $data['receiverCountrycode'] = '';
//        $data['receiverEmail'] = '';
//        $data['receiverSMSNr'] = '';
//        $data['receiverTelNr'] = '';
//        $data['weight'] = '';
//        $data['barcode'] = '';
//        app("log")->error("postnl 准备生成面单数据：".json_encode($data));
        $senderAddress['AddressType'] = self::SENDER_CODE;
        $senderAddress['FirstName'] = 'My EU Shop';
        $senderAddress['Name'] = '';
        $senderAddress['CompanyName'] = 'My Eu Shop B.V.';
        $senderAddress['Street'] = 'Pesetaweg';
        $senderAddress['HouseNr'] = '20';
        $senderAddress['Zipcode'] = '2153PJ';
        $senderAddress['City'] = 'Nieuw-Vennep';
        $senderAddress['Countrycode'] = 'NL';
        $customer['Address'] = $senderAddress;
        $customer['CollectionLocation'] = "1234506";
        $customer['ContactPerson'] = "Wang Chen";
        $customer['CustomerCode'] = POSTNL_LABEL_CUSTOMER_CODE;
        $customer['CustomerNumber'] = POSTNL_LABEL_CUSTOMER_NUMBER;
        $customer['Email'] = "info@myeushop.com";
        $customer['Name'] = "Eushop";
        $message['MessageID'] = time();
        $message['MessageTimeStamp'] = date("d-m-Y H:i:s");
        $message['Printertype'] = "GraphicFile|PDF";
        $contact['ContactType'] = self::RECEIVER_CODE;
        $contact['Email'] = $data['receiverEmail'];
        $contact['SMSNr'] = $data['receiverSMSNr'];
        $contact['TelNr'] = $data['receiverTelNr'];
        $dimension['Weight'] = $data['weight'];
        if (empty($data['receiverName'])) {
            $name = $data['receiverFirstName'];
        } else {
            $name = $data['receiverName'];
        }
        if (empty($data['receiverFirstName'])) {
            $firstName = $data['receiverName'];
        } else {
            $firstName = $data['receiverFirstName'];
        }
        $receiverAddress['Name'] = app('pinyin')->sentence($name);
        $receiverAddress['FirstName'] = '';
//        $receiverAddress['FirstName'] = app('pinyin')->sentence($firstName);
        $receiverAddress['Street'] = $data['receiverStreet'];
        $receiverAddress['HouseNr'] = $data['receiverHouseNr'];
        $receiverAddress['City'] = $data['receiverCity'];
        $receiverAddress['Zipcode'] = $data['receiverZipcode'];
        $receiverAddress['Countrycode'] = $data['receiverCountrycode'];
        $receiverAddress['AddressType'] = self::RECEIVER_CODE;
        $shipments['Addresses'] = [$receiverAddress];
        $shipments['Barcode'] = $data['barcode'];
        $shipments['Contacts'] = [$contact];
        $shipments['DeliveryAddress'] = self::RECEIVER_CODE;
        $shipments['ProductCodeDelivery'] = '3085';
        $shipments['Dimension'] = $dimension;
        $label['Customer'] = $customer;
        $label['Message'] = $message;
        $label['Shipments'] = $shipments;
        $url = env("POSTNL_API_URL") . "/shipment/v2_1/label";
        $client = new Client(['verify' => false]);
        $postData = json_encode($label);
        $curldata = [
            'headers' => [
                'apikey' => POSTNL_LABEL_API_KEY,
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($postData)
            ],
            'body' => $postData
        ];
        try {
            $response = $client->request("POST", $url, $curldata);
        } catch (TransferException $e) {
//            app("NoticeService")->reportToWechat("[" . $data['barcode'] . "]PostNl生成物流单失败", $e->getMessage());
            return ['status' => 1, 'msg' => "下单失败，PostNL系统可能出现问题了，请稍后再尝试！"];
        }
        $res = json_decode($response->getBody(), true);
        if (!isset($res['ResponseShipments'])) {
            return ['status' => 1, 'msg' => "下单失败，PostNL系统没有响应面单数据"];
        }
        $res_shipments = $res['ResponseShipments'];
        $savePath = storage_path('/labels/postnl/' . date('Ymd', strtotime($data['created_at'])));
        Flysystem::createDir($savePath);
        foreach ($res_shipments as $shipment) {
            foreach ($shipment['Labels'] as $label) {
                $data[$shipment['Barcode']] = '/labels/postnl/' . date('Ymd') . '/' . $shipment['Barcode'] . ".pdf";
                Flysystem::put($data[$shipment['Barcode']], base64_decode($label['Content']));
            }
        }
        return ['status' => 0, 'data' => $data];
    }

    /**
     * 获取当前包裹的状态，postnl说这个接口不能频繁调用
     * @param $barcode
     * @return array
     */
    public function getStatusBybarcode($barcode)
    {
        $url = env("POSTNL_API_URL") . "/shipment/v1_6/status/barcode/" . $barcode;

        $arr['detail'] = true;

        $url = $url . "?" . http_build_query($arr);


        $client = new Client(['verify' => false]);

        $response = $client->request("GET", $url, [
            'headers' => ['apikey' => POSTNL_LABEL_API_KEY],
        ]);

        $res = json_decode($response->getBody(), true);
        if (isset($res['CompleteStatus'])) {
            return $res['CompleteStatus']['Shipment'];
        } else {
            return [];
        }
    }

    private function convertToZh($code)
    {
        $codeMsg = [
            1 => '收到揽件通知',
            2 => '已收到包裹',
            3 => '揽件成功',
            4 => '揽件失败',
            5 => '包裹已分拣',
            6 => '包裹未分拣',
            7 => '正在派送中',
            8 => '包裹未送达',
            9 => '包裹到达海关',
            11 => '已签收',
            12 => '已送达取件点',
            13 => '暂未收到包裹',
            14 => '确认未收到包裹',
            15 => '揽件时找不到包裹',
            16 => '分拣时找不到包裹',
            17 => '派送时找不到包裹',
            18 => '确认包裹已失踪',
            19 => '拒绝揽件',
            20 => '正在清关中',
            21 => '包裹在仓库',
            22 => '已从邮局签收',
            23 => '已取货',
            99 => '不可用',
        ];

        if (isset($codeMsg[$code])) {
            return $codeMsg[$code];
        } else {
            app("log")->error("POSTNL 包裹物流存在新的状态：" . $code);
            return '未知的消息：' . $code;
        }
    }

}
