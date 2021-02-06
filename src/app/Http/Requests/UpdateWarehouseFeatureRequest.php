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

use App\Models\WarehouseFeature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateWarehouseFeatureRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $type = WarehouseFeature::find($this->route('feature_id'));
        return $type && $type->owner_id == Auth::ownerId();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name_cn'         => [
                'required','string','max:50',
                Rule::unique('warehouse_feature')->ignore($this->route('feature_id'))
            ],
            'name_en'         => [
                'required','string','max:50',
                Rule::unique('warehouse_feature')->ignore($this->route('feature_id'))
            ],
            'is_enabled'     => 'required|boolean',
            'logo'           => 'present|string|max:255',
            'remark'         => 'present|string|max:255',
        ];
    }
}
