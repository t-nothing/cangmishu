<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\OrderType;

class UpdateOrderTypeRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $type = OrderType::find($this->route('type_id'));
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
            'name'         => [
                'required','string','max:50',
                Rule::unique('order_type')->where(function ($query) {
                    return $query->where('owner_id',Auth::ownerId());
                })->ignore($this->route('type_id'))
            ],
            'is_enabled'   => 'required|boolean',
        ];
    }
}
