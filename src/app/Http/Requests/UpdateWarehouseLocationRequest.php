<?php

namespace App\Http\Requests;

use App\Models\WarehouseLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateWarehouseLocationRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $type = WarehouseLocation::find($this->route('location_id'));
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
            'code'         => [
                'required','string','max:50',
                Rule::unique('warehouse_location')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })->ignore($this->route('location_id')),
            ],
            'warehouse_area_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse_area','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'is_enabled'        => 'required|boolean',
            'passage'           => 'sometimes|string|max:15',
            'row'               => 'sometimes|string|max:15',
            'col'               => 'sometimes|string|max:15',
            'floor'             => 'sometimes|string|max:15',
            'remark'            => 'sometimes|string|max:255',
        ];
    }
}
