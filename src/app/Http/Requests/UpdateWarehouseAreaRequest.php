<?php

namespace App\Http\Requests;

use App\Models\WarehouseArea;
use App\Rules\AlphaNumDash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateWarehouseAreaRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $type = WarehouseArea::find($this->route('areas_id'));
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
                Rule::unique('warehouse_area')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })->ignore($this->route('areas_id'))

            ],
            'code'           => [
                'required', 'string', 'max:255',
                Rule::unique('warehouse_area')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })->ignore($this->route('areas_id'))
            ],
            'is_enabled'     => 'required|boolean',
            'functions'      => 'sometimes|array',
            'functions.*'    => 'sometimes|required|integer|min:1',
            'remark'         => 'string|max:255',
        ];
    }
}
