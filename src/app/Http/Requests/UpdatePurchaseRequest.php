<?php

namespace App\Http\Requests;

use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $purchase = Purchase::find($this->route('id'));
        return $purchase && $purchase->owner_id == Auth::ownerId();
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
            'warehouse_id.exists' => '仓库不存在',
            'purchase_code.unique' => '采购单号存在',
            'distributor_id.exists' =>'供货商不存在',
            'items.*.relevance_code.exists' =>  '外部编码不存在',
            'items.*.purchase_price.required' =>  '进货价格不能为空',
        ];
    }
}
