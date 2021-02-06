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

use App\Models\BatchType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EditBatchTypeRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $type = BatchType::find($this->route('type_id'));
        return $type && $type->owner_id == Auth::ownerId();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return[
            'name'         => [
                                'required','string','max:50',
                                Rule::unique('batch_type')->where(function ($query) {
                                    return $query->where('owner_id',Auth::ownerId());
                                })->ignore($this->route('type_id'))
                               ],
            'is_enabled'   => 'required|boolean',
        ];
    }
}
