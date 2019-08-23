<?php

namespace App\Http\Requests;

use App\Models\Batch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateBatchRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $batch = Batch::find($this->route('batch_id'));
        return $batch && $batch->owner_id == Auth::ownerId();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $warehouse_id = $this->warehouse_id;
        return [
            'warehouse_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'type_id' => [
                'required','integer','min:1',
                Rule::exists('batch_type','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'confirmation_number'              => 'string|max:255',
            'distributor_id'                   =>  [
                'integer','min:1',
                Rule::exists('distributor','id')->where(function($q){
                    $q->where('user_id',Auth::ownerId());
                })
            ],
            'plan_time'                        => 'date_format:Y-m-d H:i:s',//修改可为空
            'over_time'                        => 'date_format:Y-m-d H:i:s|after_or_equal:plan_time',//修改可为空
            'remark'                           => 'string|max:255',//修改可为空
            'product_stock'                    => 'required|array',
            'product_stock.*.relevance_code'   => [
                'required','string','max:255',
                Rule::exists('product_spec','relevance_code')->where(function($q)use ($warehouse_id){
                    $q->where('owner_id',Auth::ownerId())
                      ->where('warehouse_id',$warehouse_id);
                })
            ],
            'product_stock.*.need_num'         => 'required|integer|min:1|max:99999',
            'product_stock.*.distributor_code' => ['string','max:20','distinct'],
            'product_stock.*.remark'           => 'present|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'warehouse_id.exists' => '仓库不存在',
        ];
    }
}
