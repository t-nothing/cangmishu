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

class Modules extends  Model
{
    protected  $table = 'modules';
    protected  $fillable = ['name', 'parent_id'];
    public     $timestamps = true;
    protected $hidden=['pivot'];

    public function fromDateTime($value) {

        return parent::fromDateTime($value);
    }


    public function groups()
    {
        return $this->belongsToMany('App\Models\Groups', 'group_module_rel', 'module_id', 'group_id');
    }

}