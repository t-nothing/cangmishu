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

class SenderAddress extends  Model
{
    use SoftDeletes;
    protected $table ="sender_address";
    protected  $fillable =['fullname','phone','country','province', 'city','district','address','owner_id', 'street', 'door_no'];
    protected $guarded = [];

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }


    public function getFullAddressAttribute()
    {
        return $this->country.$this->province.$this->city.$this->district.$this->address;
    }

}