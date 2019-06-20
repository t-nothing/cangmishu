<?php

namespace App\Handlers;


use Mockery\Exception;
class  CodeService
{
    protected $code;

    //手机用户发送验证码
    public function sendCode($phone_num,$sms_type='default'){
        try{
            //验证手机号是否符合格式
            $validate_result = $this->ValidatePhoneCode($phone_num);
            if(!$validate_result){
                return [
                    'error' =>'1',
                    'message' =>'手机号码格式有误,请重新填写',
                    'check_code'=>''
                ];
            };
            //判断是否超过规定次数
            if($this->isOverMessageNumLimit($message)){
                return [
                    'error' =>'1',
                    'message' =>'该手机号码今日发送的短信已达到上限',
                    'check_code'=>''
                ];
            };

            //发送验证码
            $aliSms = new AliSms();
            $sms_code = config('aliyunsms.'.$sms_type);
            $code =$this->getCode();
            $date = date("Y-m-d") ;

            $response = $aliSms->sendSms($phone_num,$sms_code, ['code'=> $code]);
            if($response->Code === 'OK'){
                if(!$message->id){
                    $message::create(['phone_code'=>$phone_num,'count'=>1,'time'=>$date]);
                }else{
                    $message->increment('count', 1);
                    $message->save();
                }
                return [
                    'error' =>'0',
                    'message' => '短信发送成功',
                    'check_code' =>$code
                ];
            }else{
                return [
                    'error' =>'1',
                    'message' =>$response->Message,
                    'check_code'=>''
                ];
            };
        }catch(Exception $exception){
            return [
                'error' =>'1',
                'message' =>$exception->getMessage(),
                'check_code'=>''
            ];
        }

    }

    //随机生成6位验证码
    protected  function getCode(){
        $chars='0123456789';
        mt_srand((double)microtime()*1000000*getmypid());
        $checkCode="";
        while(strlen($checkCode)<6)
            $checkCode.=substr($chars,(mt_rand()%strlen($chars)),1);
        $this->code = $checkCode;
        return $this;
    }

    //获取手机用户短信当日短信发送次数
    public function  getMobileSendMessageCount($message){
        if($message){
            $count = $message->count;
        }else{
            $count = 0;
        }
        return $count;
    }

    public function isOverMessageNumLimit($phone_num){
        $count = $this->getMobileSendMessageCount($phone_num);
        $limit = config('admin.sendMessageLimit');
        if($count >=$limit){
            return TRUE;
        }else{
            return False;
        }
    }

    //    验证手机号格式
    public function ValidatePhoneCode($phone_num){
        $preg = '/^134[0-8]\d{7}$|^13[^4]\d{8}$|^14[5-9]\d{8}$|^15[^4]\d{8}$|^16[6]\d{8}$|^17[0-8]\d{8}$|^18[\d]{9}$|^19[8,9]\d{8}$/';
        if(!preg_match($preg,$phone_num)){
            return False;
        }else{
            return True;
        }
    }

}

