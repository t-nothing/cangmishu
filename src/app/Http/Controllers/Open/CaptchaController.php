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
 * 验证码.
 */

namespace App\Http\Controllers\Open;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseRequests;
use Gregwar\Captcha\CaptchaBuilder; 
use Illuminate\Support\Facades\Cache;


class CaptchaController extends Controller
{

    public function show(){

        $builder = new CaptchaBuilder();
        $builder->build();
        $data = $builder->getPhrase();

        $key = Cache::increment('CMS-CAPTCHA-KEY');
        $key = md5(md5($key).date("Ymd").'cms');
        Cache::tags(['captcha'])->put($key, $data, 60);


        app('log')->info('验证码生成', [
                'cache'=>Cache::get($key),
                'key'=>$key
            ]);


        $arr = [
            'captcha'       => $builder->inline(90),
            'captcha_key'   => $key,
        ];
        return formatRet(0, '', $arr);
      
    }


    public function valid(BaseRequests $request){
        $this->validate($request,[
            'captcha_key' =>  'required|string|min:1',
            'captcha' =>  'required|string'
        ]);


        if (strtoupper(Cache::tags(['captcha'])->get($request->captcha_key)) != strtoupper($request->captcha)) {
            return formatRet(500, message("message.failed"));
        }
        Cache::forget($request->captcha_key);

        return formatRet(0, message("message.success"));
      
    }
}