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

class CreateOrderRequest extends BaseRequests
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
        $warehouse_id = intval(app('auth')->warehouse()->id);
        // app("log")->info("仓库和所属", [
            // "warehouse_id"      =>  $warehouse_id,
            // "owner_id"          =>  Auth::ownerId(),
        // ]);
        // app('log')->info('新增出库单',$this->all());

        return [
            // 出库单数据
            'order_type'                  => [
                'required','integer','min:1',
                Rule::exists('order_type','id')->where(function($q) use($warehouse_id){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'goods_data'                  => 'required|array',
            'goods_data.*.relevance_code' => [
                'required','string',/*'distinct', @TODO: 弱类型比较可能会有问题*/
                Rule::exists('product_spec','relevance_code')->where(function($q) use($warehouse_id){
                      $q->where('owner_id',Auth::ownerId())
                        ->where('warehouse_id',$warehouse_id);
                })
            ],
            'goods_data.*.num'            => 'required|integer|min:1|max:99999',
            'goods_data.*.sale_price'     => 'required|numeric|min:0|max:99999',
            // 快递单数据
            'delivery_type'               => 'integer|min:1',
            'delivery_date'               => 'string|date_format:Y-m-d',
            'express_num'                 => 'string|max:255',
            'receiver_id'                 =>  [
                'required','integer','min:1',
                Rule::exists('receiver_address','id')->where(function($q) use($warehouse_id){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'sender_id'                   => 'integer|min:0',
            'remark'                      => 'string|max:255',
            'pay_status'                  => 'integer|min:0',
            'pay_type'                    => 'integer|min:1',
            'sub_pay'                     => 'numeric|min:0',
            'payment_account_number'      => 'string|max:100',
        ];
    }

    public function attributes()
    {
        return [
            'order_type'                        => '出库单类型',
            'delivery_date'                     => '出库日期',
            'receiver_id'                       => '收件人',
            'sender_id'                         => '发件人',
            'pay_status'                        => '支付状态',
            'pay_type'                          => '支付类型',
            'sub_pay'                           => '实付金额',
            'goods_data.*.num'                  => '出库数量',
            'goods_data.*.sale_price'           => '销售价格',
            'goods_data.*.relevance_code'       => '商品规格',
        ];
    }
}
