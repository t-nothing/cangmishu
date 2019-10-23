<?php

namespace App\Http\Requests;

use App\Models\PurchaseItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePurchaseItemRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $purchase = PurchaseItem::find($this->route('id'));
        return $purchase && $purchase->owner_id == Auth::ownerId();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'arrived_date'        => 'required|date_format:Y-m-d',//修改可为空
            'arrived_num'         => 'required|integer|min:1|max:99999',
        ];
    }
}
