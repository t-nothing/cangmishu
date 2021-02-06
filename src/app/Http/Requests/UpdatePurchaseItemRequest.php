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

use App\Models\PurchaseItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePurchaseItemRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $purchase = PurchaseItem::find($this->route('id'));
        return $purchase && $purchase->owner_id == Auth::ownerId();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'arrived_date'        => 'required|date_format:Y-m-d',//修改可为空
            'arrived_num'         => 'required|integer|min:1|max:99999',
        ];
    }
}
