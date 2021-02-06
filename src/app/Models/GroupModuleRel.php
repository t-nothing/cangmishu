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


class GroupModuleRel extends  Model
{
    protected  $table = 'group_module_rel';
    protected  $fillable = ['group_id', 'module_id'];
    public  $timestamps = true;

    public function fromDateTime($value) {

        return parent::fromDateTime($value);
    }


}