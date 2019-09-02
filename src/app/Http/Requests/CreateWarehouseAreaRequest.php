<?php

namespace App\Http\Requests;

use App\Rules\AlphaNumDash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateWarehouseAreaRequest extends BaseRequests
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
        app('log')->info('新增仓库货区', $this->all());
        return [
            'warehouse_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'name_cn'         => [
                'required','string','max:50',
                Rule::unique('warehouse_area')->where(function ($query) {
                    return $query->where('warehouse_id', $this->warehouse_id)
                                ->where('owner_id',Auth::ownerId());
                }),
            ],
            'code'           =>  [
                'required', 'string', 'max:255',
                Rule::unique('warehouse_area')->where(function ($query) {
                    return $query->where('warehouse_id', $this->warehouse_id)
                        ->where('owner_id',Auth::ownerId());
                }),
            ],
            'is_enabled'     => 'required|boolean',
            'remark'         => 'string|max:255',
        ];
    }
}
