<?php
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

        $key = Cache::increment('CMS-CAPTCHA-KEY'.date("Ymd"));
        $key = md5(md5($key).'cms');
        Cache::put($key, $data, 60);

        app('log')->info('验证码错误', [
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


        if (strtoupper(Cache::get($request->captcha_key)) != strtoupper($request->captcha)) {
            return formatRet(500, message("message.failed"));
        }
        Cache::forget($request->captcha_key);

        return formatRet(0, message("message.success"));
      
    }
}