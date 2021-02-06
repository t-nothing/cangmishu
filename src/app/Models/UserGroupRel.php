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


class UserGroupRel extends  Model
{

    protected  $table = 'user_group_rel';
    protected  $fillable = ['user_id', 'group_id'];
    public  $timestamps = true;

    public function fromDateTime($value) {
        return $value;
    }

}