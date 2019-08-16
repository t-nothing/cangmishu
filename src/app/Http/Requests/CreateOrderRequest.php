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
            'warehouse_id'                => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
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
            'goods_data.*.num'            => 'required|integer|min:1',
            'goods_data.*.sale_price'     => 'required|numeric|min:0',
            // 快递单数据
            'delivery_type'               => 'string|string|max:255',
            'express_num'                 => 'string|max:255',
            'receiver_id'                 =>  [
                'required','integer','min:1',
                Rule::exists('receiver_address','id')->where(function($q) use($warehouse_id){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'sender_id'                   =>  [
                'required','integer','min:1',
                Rule::exists('sender_address','id')->where(function($q) use($warehouse_id){
                    $q->where('owner_id',Auth::ownerId());

                })
            ],
            'remark' => 'string|max:255',
        ];
    }

}
