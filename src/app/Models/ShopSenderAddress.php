<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class ShopSenderAddress extends Model
{
    protected $table = 'shop_sender_address';
    protected $fillable  = ['shop_id', 'is_default', 'country', 'province', 'city', 'district', 'address', 'postcode', 'fullname', 'phone']; 
    protected $hidden = ['company','remark','deleted_at'];

    public function Shop()
    {
        return $this->belongsTo('App\Models\Shop', 'shop_id', 'id');
    }

}
