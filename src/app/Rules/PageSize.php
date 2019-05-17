<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PageSize implements Rule
{
    protected $other = [
        10,
        20,
        50,
        100,
        200,
    ];

    /**
     * 判断验证规则是否通过。
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return in_array($value, $this->other);
    }

    /**
     * 获取验证错误信息。
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute field does not exist in ' . implode(',', $this->other) . '.';
    }
}