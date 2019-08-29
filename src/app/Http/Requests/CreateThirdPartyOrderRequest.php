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
            'outer_id'              => 'required|string|max:255',

            'sender_fullname'              => 'required|string',
            'sender_phone'                 => 'required|string',
            'sender_country'               => 'required|string',
            'sender_province'              => 'required|string',
            'sender_city'                  => 'required|string',
            'sender_district'              => 'required|string',
            'sender_address'               => 'required|string',
            'sender_postcode'              => 'string',

            'receiver_fullname'              => 'required|string',
            'receiver_phone'                 => 'required|string',
            'receiver_country'               => 'required|string',
            'receiver_province'              => 'required|string',
            'receiver_city'                  => 'required|string',
            'receiver_district'              => 'required|string',
            'receiver_address'               => 'required|string',
            'receiver_postcode'              => 'string',
            'remark'                => 'string',
        ];
    }

}
