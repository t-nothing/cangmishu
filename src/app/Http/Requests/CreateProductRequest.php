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

class CreateProductRequest extends BaseRequests
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
        $self = $this;
        $arr =  [
            'category_id'               => [
                'required','integer','min:1',
                Rule::exists('category','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'name_cn'                   => 'required|string|max:255',
            'remark'                    => 'string|max:255',
            'photos'                    => 'string|max:255',
            'barcode'                   => 'sometimes|string|max:50',
            'specs'                     => 'required|array',
            'specs.*.name_cn'           => 'required|string|max:50',
            // 'specs.*.net_weight'        => 'present|numeric',
            'specs.*.gross_weight'      => 'present|numeric',
            'specs.*.sale_price'        => 'required|numeric|min:0',
            'specs.*.purchase_price'    => 'required|numeric|min:0',
            'specs.*.relevance_code'    => [
            'required', 'string', 'max:50', 'distinct',
                Rule::unique('product_spec','relevance_code')->where(function($q){
                    $q->where('warehouse_id', \auth('admin')->getWarehouseIdForRequest());
                })
            ],
        ];

        if($this->isRequiredLang())
        {
            $arr['name_en']         = 'required|string|max:255';
            $arr['specs.*.name_en'] = 'required|string|max:255';
        }

        return $arr;
    }


    public function attributes()
    {
        return [
            'name_cn'               => trans("message.productFieldName"),
            'category_id'           => trans("message.productFieldCategory"),
            'warehouse_id'          => trans("message.productFieldWarehouse"),
            'specs.*.name_cn'       => trans("message.productFieldSpecName"),
            'specs.*.gross_weight'  => trans("message.productFieldGrossWeight"),
            'specs.*.sale_price'    => trans("message.productFieldSalePrice"),
            'specs.*.purchase_price'=> trans("message.productFieldPurchasePrice"),
            //'specs.*.relevance_code'=> trans("message.productFieldRelevanceCode"),
            'specs.*.relevance_code'=> __('message.recountPageProductSpecSku'),
        ];
    }

}
