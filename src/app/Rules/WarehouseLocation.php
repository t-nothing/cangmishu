<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 货位每个部分不能超过两个字符
 */
class WarehouseLocation implements Rule
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
        foreach (explode('-', $value) as $v){
            if(strlen($v) >2 ){
                return false;
            }
        }
        return true;
    }

    /**
     * 获取验证错误信息。
     *
     * @return string
     */
    public function message()
    {
        return ':attribute  命名不符合规范';
    }
}
