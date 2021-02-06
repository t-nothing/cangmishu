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