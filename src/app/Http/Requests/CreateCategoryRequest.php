<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateCategoryRequest extends BaseRequests
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
            'name_cn'         => [
                'required','string','max:50',
                Rule::unique('category')->where(function ($query) {
                    return $query->where('owner_id',Auth::ownerId());
                }),
            ],
            'name_en'         => [
                'required','string','max:50',
                Rule::unique('category')->where(function ($query) {
                    return $query->where('owner_id',Auth::ownerId());
                }),
            ],
            'is_enabled'                   => 'required|boolean',
            'need_expiration_date'         => 'required|boolean',
            'need_production_batch_number' => 'required|boolean',
            'need_best_before_date'        => 'required|boolean',
        ];
    }

}
