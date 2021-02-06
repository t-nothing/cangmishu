<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * 快递公司.
 */

namespace App\Http\Controllers\Open;

use App\Http\Controllers\Controller;

class ExpressController extends Controller
{

    public function list(){

        $dataList = app('ship')->expressCompanyList();
        $arr  = [];
        foreach ($dataList as $key => $value) {
            $arr[] = [
                'code'  => $key,
                'name'  =>  $value
            ];
        }
        return formatRet(0, '', $arr);
      
    }
}