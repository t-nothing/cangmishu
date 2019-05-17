<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateWarehouseLocationRequest extends BaseRequests
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
            'warehouse_area_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse_area','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'code' => [
                'required','string','max:50',
                 Rule::unique('warehouse_location')->where(function ($query) {
                    return $query->where('warehouse_id', $this->warehouse_id)
                                ->where('owner_id',Auth::ownerId());
                 }),
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
