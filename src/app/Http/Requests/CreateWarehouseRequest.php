<?php

namespace App\Http\Requests;

use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
class CreateWarehouseRequest extends BaseRequests
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
        $owner_id = Auth::ownerId();

         return  [
                'name_cn' => [
                    'required','string','max:255',
                    Rule::unique('warehouse')->where(function($q) use ($owner_id){
                       return  $q->where('owner_id',$owner_id);
                    })
                ],
                'code' => [
                    'required','string','max:255',
                    Rule::unique('warehouse')->where(function($q) use ($owner_id){
                        return $q->where('owner_id',$owner_id);
                    })
                ],
                'area'     => 'required|numeric',
                'city'     => 'sometimes|string|nullable',
                'street'   => 'sometimes|string|nullable',
                'door_no'  => 'sometimes|string|nullable',
                'province' => 'sometimes|string|nullable',
            ];
    }
}
