<?php
/**
 * Created by PhpStorm.
 * User: NLE-Tech
 * Date: 2019/5/10
 * Time: 14:23
 */

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BaseRequests extends  FormRequest
{

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['code'=>422,'msg'=>$validator->errors(),'data'=>null],
            422));
    }


    protected function failedAuthorization()
    {
        throw new AccessDeniedHttpException('无权进行操作');
    }


    public function expectsJson()
    {
        return true;
    }
    public function wantsJson()
    {
        return true;
    }


    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }
}