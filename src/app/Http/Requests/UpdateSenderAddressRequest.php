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

use App\Models\SenderAddress;
use Illuminate\Support\Facades\Auth;

class UpdateSenderAddressRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $address = SenderAddress::find($this->route('address_id'));
        $owner_id = Auth::ownerId();
        return $address && $owner_id == $address->owner_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'fullname' => 'required|string',
            'phone'    => 'required|string',
            'country'  => 'string',
            'province' => 'required|string',
            'city'     => 'required|string',
            'district' => 'required|string',
            'address'  => 'required|string',
            'postcode' => 'string',
            'door_no'  => 'string',
            'street'   => 'string'
        ];
    }
}
