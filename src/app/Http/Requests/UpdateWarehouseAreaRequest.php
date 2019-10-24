<?php

namespace App\Http\Requests;

use App\Models\WarehouseArea;
use App\Rules\AlphaNumDash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateWarehouseAreaRequest extends BaseRequests
{
    var $info;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->info = WarehouseArea::find($this->route('areas_id'));
        return $this->info && $this->info->owner_id == Auth::ownerId();
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
                    return $q->where('warehouse_id', $this->info->warehouse_id)
                                ->where('owner_id',Auth::ownerId());
                })->ignore($this->route('areas_id'))

            ],
            'code'           => [
                'required', 'string', 'max:255',
                Rule::unique('warehouse_area')->where(function($q){
                    return $q->where('warehouse_id', $this->info->warehouse_id)
                                ->where('owner_id',Auth::ownerId());
                })->ignore($this->route('areas_id'))
            ],
            'is_enabled'     => 'required|boolean',
            'functions'      => 'sometimes|array',
            'functions.*'    => 'sometimes|required|integer|min:1',
            'remark'         => 'string|max:255',
        ];
    }

    // public function messages()
    // {
    //     return [
    //         'warehouse_id.exists' => '仓库不存在',
    //         'remark.required' => '备注不能为空'
    //     ];
    // }

    public function attributes()
    {
        return [
            'name_cn'               => trans("message.warehouseAreaFieldNameCn"),
            'code'                  => trans("message.warehouseAreaFieldNameCode"),
            'is_enabled'            => trans("message.warehouseAreaFieldNameIsEnabled"),
        ];
    }
}
