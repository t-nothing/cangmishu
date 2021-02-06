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

use App\Models\Batch;
use App\Models\Groups;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateGroupRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $group = Groups::find($this->route('group_id'));
        return $group && $group->user_id == Auth::ownerId();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'                             =>[
                'required','string',
                Rule::unique('groups','name')->ignore($this->route('group_id'))
            ],
            'remark'                            => 'present|string|max:255',
        ];
    }

}
