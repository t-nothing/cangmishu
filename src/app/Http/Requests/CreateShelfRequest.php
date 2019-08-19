<?php


namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateShelfRequest extends BaseRequests
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
            'batch_id'                        => [
                'required','integer','min:1',
                Rule::exists('batch','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'warehouse_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'stock'                           => 'required|array',
            'stock.*'                         => 'required|array',
            'stock.*.stock_id' 				  => [
                'required','integer','min:1',
                Rule::exists('batch_product','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'stock.*.stockin_num' 		      => 'required|integer|min:',
            'stock.*.box_code' 		          => 'sometimes|string',
            'stock.*.distributor_code' 		  => 'sometimes|string|max:255',
            'stock.*.ean' 				      => 'required|string|max:255',
            'stock.*.expiration_date' 		  => 'date_format:Y-m-d',
            'stock.*.best_before_date'        => 'date_format:Y-m-d',
            'stock.*.production_batch_number' => 'string|max:255',
            'stock.*.remark'                  => 'present|string|max:255',
            'stock.*.code'                    => [
            'required','string','max:255',
                Rule::exists('warehouse_location','code')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
        ];
    }
}