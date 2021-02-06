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

class CreateThirdPartyOrderRequest extends BaseRequests
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
            'items'                  => 'required|array',
            'items.*.sku'            => 'required|string|distinct',
            'items.*.qty'            => 'required|integer|min:1|max:99999',
            'items.*.sale_price'     => 'required|numeric|min:0|max:99999',
            'items.*.sale_currency'  => 'string|in:CNY,USD,EUR',
            // 快递单数据
            'out_sn'                => 'string|max:50|min:1',

            'sender_fullname'       => 'required|string',
            'sender_phone'          => 'required|string',
            'sender_country'        => 'required|string',
            'sender_province'       => 'required|string',
            'sender_city'           => 'required|string',
            'sender_district'       => 'required|string',
            'sender_address'        => 'required|string',
            'sender_postcode'       => 'string|max:8',

            'receiver_fullname'     => 'required|string',
            'receiver_phone'        => 'required|string',
            'receiver_country'      => 'required|string',
            'receiver_province'     => 'required|string',
            'receiver_city'         => 'required|string',
            'receiver_district'     => 'required|string',
            'receiver_address'      => 'required|string',
            'receiver_postcode'     => 'string|max:8',
            'remark'                => 'string|max:500',
            'source'                => 'string|max:10',
        ];
    }

    public function attributes()
    {
        return [
            'items'                     =>  '下单明细',
            'items.*.qty'               =>  '下单明细数量',
            'items.*.sku'               =>  '下单明细SKU',
            'items.*.sale_price'        =>  '下单明细价格',
            'items.*.sale_currency'     =>  '下单明细货币',
            'sender_fullname'   =>  '发件姓名',
            'sender_phone'      =>  '发件人电话',
            'sender_country'    =>  '发件人国家',
            'sender_province'   =>  '发件人省',
            'sender_city'       =>  '发件人市',
            'sender_district'   =>  '发件人区',
            'sender_address'    =>  '发件人详细地址',
            'sender_postcode'   =>  '发件人邮编',
            'receiver_fullname'   =>  '发件姓名',
            'receiver_phone'      =>  '发件人电话',
            'receiver_country'    =>  '发件人国家',
            'receiver_province'   =>  '发件人省',
            'receiver_city'       =>  '发件人市',
            'receiver_district'   =>  '发件人区',
            'receiver_address'    =>  '发件人详细地址',
            'receiver_postcode'   =>  '发件人邮编',
            'source'   =>  '来源',
            'remark'   =>  '下单备注',
            'out_sn'   =>   '外部订单号'
        ];
    }


}
