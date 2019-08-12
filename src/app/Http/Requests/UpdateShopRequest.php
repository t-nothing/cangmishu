<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Shop;

class UpdateShopRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $shop = Shop::find($this->route('id'));
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
            'warehouse_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'name_cn'                   => [
               'required','string','max:100',
                Rule::unique('shop', 'name_cn')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })->ignore($this->route('id'))
            ],
            'logo'                      => 'somtimes|url|max:100',
            'banner_background'         => 'somtimes|url|max:100',
            'default_lang'              => 'somtimes|string|in:zh-cn,english',
            'default_currency'          => 'somtimes|string|in:CNY,EUR,USD',
            'email'                     => 'somtimes|email|max:100',
            'remark'                    => 'string',
            'contact'                   => 'required|array',
            'contact.fullname'          => 'required|string',
            'contact.phone'             => 'required|string',
            'contact.country'           => 'required|string',
            'contact.province'          => 'required|string',
            'contact.city'              => 'required|string',
            'contact.district'          => 'required|string',
            'contact.address'           => 'required|string',
        ];

        if($this->isRequiredLang())
        {
            $arr['name_en']         = [
                'required','string','max:50',
                Rule::unique('shop', 'name_en')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ];
        }


        return $arr;
    }

}
