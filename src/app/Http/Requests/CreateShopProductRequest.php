<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Shop;

class CreateShopProductRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $shop = Shop::find($this->route('shopId'));
        return $shop && $shop->owner_id == Auth::ownerId();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $arr = [
            'warehouse_id'              => 'required|int|min:1',
            'products'                  => 'required|array|max:100',
            'products.*'                => 'required|int|min:1',
        ];

        return $arr;
    }

}
