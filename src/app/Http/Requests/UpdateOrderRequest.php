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

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $order = Order::find($this->route('order_id'));
        return $order && $order->owner_id == Auth::ownerId() && $order->status == 1;
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
            'goods_data.*.num'            => 'required|integer|min:1',
            // 快递单数据
            'delivery_date'               => 'required|string|date_format:Y-m-d',
            'delivery_type'               => 'int|min:1',
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
        ];
    }

}
