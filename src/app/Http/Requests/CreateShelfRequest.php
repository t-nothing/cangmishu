<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        app('log')->info('入库上架', $this->all());
        return [
            'batch_id'                        => [
                'required','integer','min:1',
                Rule::exists('batch','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'auto_create_location'            => 'sometimes|integer',
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
            'stock.*.ean' 				      => 'sometimes|string|max:255',
            'stock.*.expiration_date' 		  => 'date_format:Y-m-d|nullable',
            'stock.*.best_before_date'        => 'date_format:Y-m-d|nullable',
            'stock.*.production_batch_number' => 'string|max:255|nullable',
            'stock.*.remark'                  => 'sometimes|string|max:255',
            'stock.*.code'                    => 'required|string|max:255',
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