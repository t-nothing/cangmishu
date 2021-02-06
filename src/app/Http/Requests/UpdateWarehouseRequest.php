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

use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
class UpdateWarehouseRequest extends BaseRequests
{


    public function authorize()
    {
        $warehouse = Warehouse::find($this->route('warehouse_id'));
        return $warehouse && $warehouse->owner_id == Auth::ownerId();
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name_cn' => [
                'required','string','max:255',
                Rule::unique('warehouse')->where(function ($query) {
                            return $query->where('owner_id',Auth::ownerId());
                    })
                    ->ignore($this->route('warehouse_id'))
            ],
            // 'code' => [
            //     'required','string','max:255',
            //     Rule::unique('warehouse')
            //         ->where(function ($query) {
            //                 return $query->where('owner_id',Auth::ownerId());
            //          })
            //         ->ignore($this->route('warehouse_id'))
            // ],
            'area'     => 'required|numeric',
            'city'     => 'required|string',
            'street'   => 'required|string',
            'door_no'  => 'required|string',
            'province' => 'required|string',
            // 'is_enabled_lang' => 'required|int|max:1|min:0',
        ];
    }
}
