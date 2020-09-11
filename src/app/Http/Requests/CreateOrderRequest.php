<?php

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
        $warehouse_id = $this->warehouse_id;

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
                'required','string','distinct',
                Rule::exists('product_spec','relevance_code')->where(function($q) use($warehouse_id){
                      $q->where('owner_id',Auth::ownerId())
                        ->where('warehouse_id',$warehouse_id);
                })
            ],
            'goods_data.*.num'            => 'required|integer|min:1|max:99999',
            'goods_data.*.sale_price'     => 'required|numeric|min:0|max:99999',
            // 快递单数据
            'delivery_type'               => 'integer|min:1',
            'express_num'                 => 'string|max:255',
            'receiver_id'                 =>  [
                'required','integer','min:1',
                Rule::exists('receiver_address','id')->where(function($q) use($warehouse_id){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'sender_id'                   => 'integer|min:0',
            'remark'                      => 'string|max:255',
        ];
    }

    public function attributes()
    {
        return [
            'order_type'                        => '出库单类型',
            'receiver_id'                       => '收件人',
            'sender_id'                         => '发件人',
            'goods_data.*.num'                  => '出库数量',
            'goods_data.*.sale_price'           => '销售价格',
            'goods_data.*.relevance_code'       => '商品规格',
        ];
    }
}
