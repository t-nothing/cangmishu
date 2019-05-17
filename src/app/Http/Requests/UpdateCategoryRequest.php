<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $type = Category::find($this->route('category_id'));
        return $type && $type->owner_id == Auth::ownerId();
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
                Rule::unique('category')->ignore($this->route('category_id')),
            ],
            'name_en'         => [
                'required','string','max:50',
                Rule::unique('category')->ignore($this->route('category_id')),
            ],
            'is_enabled'                   => 'required|boolean',
            'need_expiration_date'         => 'required|boolean',
            'need_production_batch_number' => 'required|boolean',
            'need_best_before_date'        => 'required|boolean',
        ];
    }
}
