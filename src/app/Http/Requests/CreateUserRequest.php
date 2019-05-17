<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CreateUserRequest extends BaseRequests
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
            'email'    =>  ['required','email',Rule::unique('user','email')],
            'password' => 'required|string|min:6',
            'code'     => 'string|min:6',
            'warehouse_name' =>  ['required','string','min:4',Rule::unique('warehouse','name_cn')],
            'warehouse_area' => 'required|numeric',
        ];
    }

}
