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

use App\Rules\PageSize;
use Illuminate\Validation\Rule;

class IndexProductRequest extends BaseRequests
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
            'page' => 'integer|min:1',
            'page_size' => new PageSize(),
            'category_id' =>[
                'integer','min:1',
                Rule::exists('category','id')->where(function($q){
                    $q->where('owner_id',app('auth')->ownerId());
                })
            ],
            'created_at_b' => 'date:Y-m-d',
            'created_at_e' => 'date:Y-m-d',
            'recount' => 'integer|min:0|max:1',
            'keywords' => 'sometimes|string',
            'show_low_stock' => 'sometimes|boolean',
        ];
    }
}
