<?php
/*
 * 邮件验证码
 */
namespace App\Rules;
use App\Services\VerificationCode;
use Illuminate\Support\Arr;

/**
 * Class EmailVerifyCodeValidator.
 */
class EmailVerifyCode
{
    public function validate($attribute, $value, $parameters, $validator)
    {

        $email = Arr::get($validator->getData(), $parameters[0], null);

        if (app(VerificationCode::class)->validate($email, $value)) {
            // \requests()->merge(['email_code_verified' => true]);
            return true;
        }
        return false;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'validation.verify_code' => 'A title is required',
            'body.required'  => 'A message is required',
        ];
    }
}
