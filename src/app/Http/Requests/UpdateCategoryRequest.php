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

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends BaseRequests
{
    var $modelData;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->modelData = Category::find($this->route('category_id'));
        $this->warehouseId = $this->modelData->warehouse_id?? 0;
        return $this->modelData && $this->modelData->owner_id == Auth::ownerId();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $arr = [
            'name_cn'         => [
                'required','string','max:50',
                Rule::unique('category')->where(function ($query) {
                    return $query->where('owner_id',Auth::ownerId());
                })
                    ->ignore($this->route('category_id'))
            ],
            'is_enabled'                   => 'required|boolean',
            'need_expiration_date'         => 'required|boolean',
            'need_production_batch_number' => 'required|boolean',
            'need_best_before_date'        => 'required|boolean',
        ];

        if($this->isRequiredLang())
        {
            $arr['name_en']         = [
                'required','string','max:50',
                Rule::unique('category','name_en')->where(function ($query) {
                    return $query->where('owner_id',Auth::ownerId());
                })->ignore($this->route('category_id'))
            ];
        }
        return $arr;
    }
}
