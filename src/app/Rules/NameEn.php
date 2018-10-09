<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 只能含数字、字母、短横线、下划线、小数点、空格, 以数字字母开头
 */
class NameEn implements Rule
{
    /**
     * 判断验证规则是否通过。
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match("/^[a-zA-Z0-9\p{Han}]{1}[-a-zA-Z0-9_ \.]+$/u", $value) > 0;
    }

    /**
     * 获取验证错误信息。
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute may only contain letters, numbers, and dashes.';
    }
}
