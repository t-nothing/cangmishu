<?php

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
