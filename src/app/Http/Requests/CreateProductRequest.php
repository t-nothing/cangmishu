<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateProductRequest extends BaseRequests
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
            'warehouse_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'category_id'               => [
                'required','integer','min:1',
                Rule::exists('category','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId())
                      ->where('is_enabled',1);
                })
            ],
            'name_cn'                   => 'required|string|max:255',
            'name_en'                   => 'required|string|max:255',
            'remark'                    => 'string|max:255',
            'photos'                    => 'string|max:255',
            'specs'                     => 'required|array',
            'specs.*.name_cn'           => 'required|string|max:255',
            'specs.*.name_en'           => 'required|string|max:255',
            'specs.*.net_weight'        => 'present|numeric',
            'specs.*.gross_weight'      => 'present|numeric',
            'specs.*.relevance_code'    => [
            'required','string','max:255','distinct',
                Rule::unique('product_spec','relevance_code')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'specs.*.is_warning' =>'required|boolean'
        ];
    }
}
