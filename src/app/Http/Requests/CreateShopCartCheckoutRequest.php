<?php

namespace App\Http\Requests;



class CreateShopCartCheckoutRequest extends BaseRequests
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
        return [
            'id'            => 'required|array',
            'id.*'          => 'required|string',
            'verify_money'  => 'required|numeric|min:0',
            'fullname'      => 'required|string',
            'phone'         => 'required|string',
            'country'       => 'required|string',
            'province'      => 'required|string',
            'city'          => 'required|string',
            'district'      => 'required|string',
            'address'       => 'required|string',
            'postcode'      => 'string',
            'remark'        => 'string'
        ];
    }
}
