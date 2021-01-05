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
            'name_cn'                   => [
               'required','string','max:100',
                Rule::unique('shop', 'name_cn')->where(function($q){
                    $q->where('owner_id',Auth::ownerId())->where('deleted_at', null);
                })->ignore($this->route('id'))
            ],
            'logo'                      => 'present|url|max:100',
            'banner_background'         => 'url|max:150',
            'default_lang'              => 'string|in:zh-cn,english',
            'default_currency'          => 'string|in:CNY,EUR,USD',
            'email'                     => 'email|max:100',
            'remark'                    => 'required|string',
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


    public function attributes()
    {
        return [
            'name_cn'               => '店铺名称',
            'logo'                  => '店铺标识',
            'default_lang'          => '默认语言',
            'default_currency'      => '默认货币',
            'remark'                => '店铺简介',
            'contact.fullname'      => '店主姓名',
            'contact.phone'         => '店主联系方式',
            'contact.country'       => '店主国家',
            'contact.province'      => '店主省',
            'contact.city'          => '店主市',
            'contact.district'      => '店主区',
            'contact.address'       => '店主详细地址',
        ];
    }

}
