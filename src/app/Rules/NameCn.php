<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 只能含中文、数字、字母、短横线、下划线、小数点、空格, 并且以中文数字字母开头
 */
class NameCn implements Rule
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
        return preg_match("/^[a-zA-Z0-9\p{Han}]{1}[-a-zA-Z0-9_ \.\p{Han}]+$/u", $value) > 0;
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
