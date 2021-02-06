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


use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateRecountRequest extends BaseRequests
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
            'stock'                    => 'required|array',
            'stock.*.id'               => 'required|integer|min:1',
            'stock.*.num'              => 'required|integer|min:0|max:99999',
            'remark'                   => 'present|string',
        ];
    }
}
