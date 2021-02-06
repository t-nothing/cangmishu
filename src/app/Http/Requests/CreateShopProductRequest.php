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
use App\Models\Shop;

class CreateShopProductRequest extends BaseRequests
{
    public $modelData;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->modelData = Shop::find($this->route('shopId'));
        return $this->modelData && $this->modelData->owner_id == Auth::ownerId();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $arr = [
            // 'warehouse_id'              => 'required|int|min:1',
            'products'                  => 'required|array|max:100',
            'products.*'                => 'required|int|min:1',
        ];

        return $arr;
    }

}
