<?php
namespace  App\Services;

use App\Models\ProductSpec;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;


class ShipService
{
    
    var $expressCompany  =   array (
            'ems'       =>'EMS快递',
            'shentong'  =>'申通快递',
            'shunfeng'  =>'顺丰快递',
            'yuantong'  =>'圆通快递',
            'yunda'     =>'韵达快递',
            'huitong'   =>'百世汇通',
            'tiantian'  =>'天天快递',
            'zhongtong' =>'中通快递',
            'zhaijisong'=>'宅急送',
            'pingyou'   =>'中国邮政',
            'quanfeng'  =>'全峰快递',
            'guotong'   =>'国通快递',
            'jingdong'  =>'京东快递',
            'sure'      =>'速尔快递',
            'zhongtie'  =>'中铁快运',
            'yousu'     =>'优速快递',
            'longbang'  =>'龙邦快递',
            'debang'    =>'德邦物流',
            'fedex'     =>'Fedex',
            'ups'       =>'UPS',
            'tnt'       =>'TNT',
            'nlexpress' =>'NLE荷兰快递',
            'other'     =>  '其他'
          );

    function getExpressName($code) {
        return $this->expressCompany[$code]??'';
    }

    /**
     * 快递列表
     **/
    function expressCompanyList(){

        return $this->expressCompany;
    }

    /**
     * 验证快递公司
     **/
    function validCode($code){
        return isset($this->expressCompany[$code]);
    }
}