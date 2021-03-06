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

class CreateCategoryRequest extends BaseRequests
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
        $warehouse_id = app('auth')->warehouse()->id;
        $arr = [
            'name_cn'         => [
                'required','string','max:50',
                Rule::unique('category')->where(function ($query) use($warehouse_id) {
                    return $query->where('owner_id',Auth::ownerId())->where('warehouse_id',$warehouse_id);
                }),
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
                Rule::unique('category')->where(function ($query) {
                    return $query->where('owner_id',Auth::ownerId());
                }),
            ];
        }


        return $arr;
    }

}
