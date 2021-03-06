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
use App\Models\ShopProduct;

class UpdateShopProductRequest extends BaseRequests
{
    var $shopProduct;

    public function getShopProduct()
    {
        return $this->shopProduct;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->shopProduct = ShopProduct::with("shop")->find($this->route('id'));
        return $this->shopProduct && $this->shopProduct->shop->owner_id == Auth::ownerId() && $this->shopProduct->shop->id == $this->route('shopId');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $productId = $this->getShopProduct()->id;
        $arr = [
            'name_cn'                   => 'required|string|max:50',
            'remark'                    => 'present|string',
            'pics'                      => 'present|array',
            'pics.*'                    => 'required|url', 
            'descs'                     => 'present|array',
            'descs.*'                   => 'required|url', 
            'specs'                     => 'present|array',
            'specs.*.name_cn'           => 'required|string|min:1|max:20',
            'specs.*.sale_price'        => 'required|numeric|min:0|max:99999',
            'specs.*.is_shelf'           => 'required|integer|min:0|max:1',
            'specs.*.id'   => [
                'required','int','min:1',
                Rule::exists('shop_product_spec','id')->where(function($q)use ($productId){
                    $q->where('shop_product_id',$productId);
                })
            ],
        ];

        if($this->isRequiredLang())
        {
            $arr['name_en']         = [
                'required','string','max:50'
            ];
            $arr['specs.*.name_en']         = [
                'required','string','max:20'
            ];
        }


        return $arr;
    }

    public function messages()
    {
        return [
            'specs.*.id.exists' =>  '规格不存在',
        ];
    }

}
