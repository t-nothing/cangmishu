<?php
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