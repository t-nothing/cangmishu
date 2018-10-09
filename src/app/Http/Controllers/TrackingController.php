<?php
/**
 * DESC:物流相关接口
 * Author: YangBin
 * DateTime: 2017/12/15 13:15
 * Email: yangbin@nle-tech.com
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class TrackingController extends Controller{

    public function getTrackInfo(Request $request){
        $this->validate($request, [
            'express_num' => 'required|string',
        ]);
        $expressNum = $request->express_num;
        $data = app("TrackingService")->getExpress($expressNum);
        if ($data['status'] == 1) {
            return formatRet(0, $data['tips'],$data['data']);
        } else {
            return formatRet(1, $data['tips']);
        }
    }

}