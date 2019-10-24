<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreatePurchaseRequest extends BaseRequests
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
            'warehouse_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'purchase_code'              => ['required','string','max:50',
                Rule::unique('purchase', 'purchase_code')->where(function($q) use($warehouse_id) {
                    $q->where('warehouse_id', $warehouse_id);
                })
            ],           
            'order_invoice_number'             => ['string','max:50'],
            'distributor_id'                   => [
                'required','integer','min:1',
                Rule::exists('distributor','id')->where(function($q){
                    $q->where('user_id',Auth::ownerId());
                })
            ],
            'created_date'                      => 'required|date_format:Y-m-d',//修改可为空
            'remark'                            => 'string|max:255',//修改可为空
            'items'                             => 'required|array',
            'items.*.relevance_code'            => [
                'required','string','max:255',
                Rule::exists('product_spec','relevance_code')->where(function($q)use ($warehouse_id){
                    $q->where('owner_id',Auth::ownerId())
                      ->where('warehouse_id',$warehouse_id);
                })
            ],
            'items.*.need_num'         => 'required|integer|min:1|max:99999',
            'items.*.purchase_price'   => 'required|numeric|min:0|max:99999',
        ];
    }

    public function messages()
    {
        return [
            'warehouse_id.exists' => trans("message.batchPurchaseWarehouseNotExists"),
            'purchase_code.unique' => trans("message.batchPurchaseCodeExists"),
            'distributor_id.exists' =>trans("message.batchPurchaseSupplierNotExists"),
            'items.*.relevance_code.exists' =>  trans("message.batchPurchaseOuterCodeNotExists"),
            'items.*.purchase_price.required' =>  trans("message.batchPurchasePriceRequired"),
        ];
    }
}
