<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services;

use App\Models\Modules;

class ModuleService
{
    public static function getModulesByUser($user, $warehouse_id = null)
    {
        $modules = [];
        //判断用户是否为员工
        if ($user->boss_id) { //是员工
            //获得员工所在分组的权限，如果有分组的话
            if ($warehouse_id) {
                $modules = $user->groups()->where('warehouse_id',
                    $warehouse_id)->with(['modules'])->get()->pluck('modules')->flatten()->toArray();
            }
        } else {
            $modules = Modules::get()->toArray();
        }

        return $modules;
    }

    public static function getModuleTree($modules, $pid){
        $ret = [];
        foreach ($modules as $k=>$v){
            if($v['parent_id'] == $pid){
                $data = self::getModuleTree($modules,$v['id']);
                if(count($data)){
                    $v['children']=$data;
                }
                $ret[]=$v;
            }
        }
        return $ret;
    }

    public static function getModules($group_id =null){
        return Modules::when($group_id,function ($query) use ($group_id){
            $query->where('group_id',$group_id)->where('status','>',0);
        })->get();
    }
}
