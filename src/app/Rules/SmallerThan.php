<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 字段a小于指定字段b
 */
class SmallerThan implements Rule
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
