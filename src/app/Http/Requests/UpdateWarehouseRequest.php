<?php

namespace App\Http\Requests;

use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
class UpdateWarehouseRequest extends BaseRequests
{


    public function authorize()
    {
        $warehouse = Warehouse::find($this->route('warehouse_id'));
        return $warehouse && $warehouse->owner_id == Auth::ownerId();
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name_cn' => [
                'required','string','max:255',
                Rule::unique('warehouse')->ignore($this->route('warehouse_id'))
            ],
            'code' => [
                'required','string','max:255',
                Rule::unique('warehouse')->ignore($this->route('warehouse_id'))
            ],
            'area'     => 'required|numeric',
            'city'     => 'sometimes|string|nullable',
            'street'   => 'sometimes|string|nullable',
            'door_no'  => 'sometimes|string|nullable',
            'province' => 'sometimes|string|nullable',
        ];
    }
}
