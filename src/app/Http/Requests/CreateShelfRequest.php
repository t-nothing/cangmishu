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
        // app('log')->info('入库上架', $this->all());
        return [
            'batch_id'                        => [
                'required','integer','min:1',
                Rule::exists('batch','id')->where(function($q){
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
            // 'stock.*.expiration_date' 		  => 'date_format:Y-m-d',
            // 'stock.*.best_before_date'        => 'date_format:Y-m-d',
            'stock.*.production_batch_number' => 'string|max:255',
            'stock.*.remark'                  => 'sometimes|string|max:255',
            'stock.*.code'                    => [
            'required','string','max:255',
                Rule::exists('warehouse_location','code')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
        ];
    }

    public function attributes()
    {
        return [
            'batch_id'              => 'ID',
            'stock'                 => '上架清单',
            'stock.*.stock_id'      =>  '上架清单明细',
            'stock.*.stockin_num'   =>  '上架清单数量',
            'stock.*.ean'           =>  '上架EAN码',
            'stock.*.expiration_date'                   =>  '上架过期时间',
            'stock.*.best_before_date'                  =>  '上架最佳食用期',
            'stock.*.production_batch_number'           =>  '上架生产批次号',
            'stock.*.code'                              =>  '上架货位',
        ];
    }
}