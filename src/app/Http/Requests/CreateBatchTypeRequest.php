<?php

namespace App\Http\Requests;


use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateBatchTypeRequest extends BaseRequests
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
        $warehouse_id = app('auth')->warehouse()->id;
        return [
            'warehouse_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q) use($warehouse_id){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'name'         => [
                'required','string','max:50',
                Rule::unique('batch_type')->where(function ($query) use($warehouse_id) {
                    return $query->where('owner_id',Auth::ownerId())->where('warehouse_id',$warehouse_id);
                }),
            ],
            'is_enabled'   => 'required|boolean',
        ];
    }
}
