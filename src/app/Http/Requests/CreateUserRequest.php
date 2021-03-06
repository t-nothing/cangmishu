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

use Illuminate\Validation\Rule;

class CreateUserRequest extends BaseRequests
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
            'type'              => ['required',Rule::in(['email', 'mobile', 'wechat'])],
            'mobile'            => ['required_if:type,mobile','mobile',Rule::unique('user','phone')],
            'email'             => ['required_if:type,email','email',Rule::unique('user','email')],
            'password'          => 'required|string|min:6',
            'code'              => 'required|numeric|min:4',
            'warehouse_name'    => 'string|max:20',
            'warehouse_area'    => 'numeric',
        ];
    }

}
