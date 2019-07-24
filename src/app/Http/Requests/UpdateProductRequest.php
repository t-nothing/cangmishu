<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class UpdateProductRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $product = Product::find($this->route('product_id'));
        $this->warehouseId = $product->warehouse_id?? 0;
        return $product && $product->owner_id == Auth::ownerId();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $arr = [
            'category_id'               => [
                'required','integer','min:1',
                Rule::exists('category','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId())
                        ->where('is_enabled',1);
                })
            ],
            'name_cn'                   => 'required|string|max:255',
            'remark'                    => 'string|max:255',
            'photos'                    => 'string|max:255',
            'specs'                     => 'required|array',
            'specs.*.relevance_code'    => ['required','string'],
            'specs.*.name_cn'           => 'required|string|max:255',
            'specs.*.net_weight'        => 'present|numeric',
            'specs.*.gross_weight'      => 'present|numeric',
            'specs.*.is_warning'        => 'required|boolean',
        ];

        if($this->isRequiredLang($this->warehouseId))
        {
            $arr['name_en']         = 'required|string|max:255';
            $arr['specs.*.name_en'] = 'required|string|max:255';
        }
        return $arr;
    }
}
