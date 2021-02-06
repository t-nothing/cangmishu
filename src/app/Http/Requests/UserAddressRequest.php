<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:50',
            'phone' => 'required|string|max:80',
            'address' => 'required|string|max:180',
            'is_default' => 'sometimes|nullable|in:0,1',
        ];
    }

    public function attributes()
    {
        return [
            'name' => '联系人',
            'phone' => '手机号',
            'address' => '详细地址',
            'is_default' => '设置为默认地址',
        ];
    }
}
