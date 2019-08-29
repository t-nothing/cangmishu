<?php

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
        $warehouse_id = $this->warehouse_id;

        return [
            'item'                  => 'required|array',
            'item.*.sku'            => 'required|string|distinct',
            'item.*.num'            => 'required|integer|min:1|max:99999',
            'item.*.sale_price'     => 'required|numeric|min:0|max:99999',
            'item.*.sale_currency'  => 'string',
            // 快递单数据
            'out_sn'                => 'string|max:255',
            'fullname'              => 'required|string',
            'phone'                 => 'required|string',
            'country'               => 'required|string',
            'province'              => 'required|string',
            'city'                  => 'required|string',
            'district'              => 'required|string',
            'address'               => 'required|string',
            'postcode'              => 'string',
            'remark'                => 'string',
        ];
    }

}
