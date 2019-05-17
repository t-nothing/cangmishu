<?php

namespace App\Http\Requests;

use App\Rules\PageSize;
use Illuminate\Validation\Rule;

class IndexProductRequest extends BaseRequests
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
                    $q->where('owner_id',app('auth')->ownerId());
                })
            ],
            'page' => 'integer|min:1',
            'page_size' => new PageSize(),
            'category_id' =>[
                'integer','min:1',
                Rule::exists('category','id')->where(function($q){
                    $q->where('owner_id',app('auth')->ownerId());
                })
            ],
            'updated_at_b' => 'date:Y-m-d',
            'updated_at_e' => 'date:Y-m-d',
            'keywords' => 'sometimes|string'
        ];
    }
}
