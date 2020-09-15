<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Validation\Rule;

class PickAndOutRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        app('log')->info('一键出库',$this->all());
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
        return [
            'order_id'              => [
                'required','integer','min:1',
                Rule::exists('order','id')->where(function($q) use($warehouse_id){
                    $q->where('owner_id',app('auth')->ownerId())
                        ->where('status',Order::STATUS_DEFAULT)
                        ->where('warehouse_id',$warehouse_id);
                })
            ],
            'express_code'           => 'string|string|max:255',
            'express_num'            => 'string|max:255',
            'delivery_date'          => 'required|string|date_format:Y-m-d',
            'items'                  => 'required|array',
            'items.*.order_item_id'  => [
                'required','integer','min:1',
                Rule::exists('order_item','id')->where(function($q){
                    $q->where('order_id',$this->order_id)
                      ->where('owner_id',app('auth')->ownerId());
                })
            ],
            'items.*.pick_num'       => 'required|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'warehouse_id.exists' => '仓库不存在',
            'batch_code.unique'  => '入库单编码重复',
        ];
    }
}
