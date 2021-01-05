<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GetThirdPartyOrderRequest extends BaseRequests
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
            'out_sn'                => 'string|max:50|min:1',
        ];
    }

    public function attributes()
    {
        return [
            'out_sn'   =>   '外部订单号'
        ];
    }


}
