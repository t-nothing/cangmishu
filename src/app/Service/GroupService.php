<?php

namespace App\Services;

use App\Models\GroupModuleRel;
use App\Models\Groups;
use App\Models\UserGroupRel;
use Illuminate\Support\Facades\Auth;

class GroupService
{
    public function getGroupsByUser($page_size, $user){
        $groups = Groups::where('user_id',$user->id);
        return $groups->paginate($page_size)->toArray();
    }

    public function updateAttribute($group_id,$name, $remark)
    {
        return Groups::where('id', $group_id)->update(['name' => $name,'remark' => $remark]);
    }

    public  function  updateRelatedModules($new, $group_id)
    {
        $old = GroupModuleRel::where('group_id', $group_id)->pluck('module_id')->toArray();
        $remove = array_diff($old, $new);
        $add = collect(array_diff($new, $old))->map(function ($v) use ($group_id){
            return ['group_id' =>$group_id, 'module_id' =>$v];
        })->toArray();
        GroupModuleRel::where('group_id', $group_id)->whereIn('module_id', $remove)->delete();
        GroupModuleRel::insert($add);
    }

    public function deleteGroup($group_id)
    {
        Groups::where('id',$group_id)->forceDelete();
        GroupModuleRel::where('group_id',$group_id)->forceDelete();
        UserGroupRel::where('group_id',$group_id)->forceDelete();
    }

    public function createGroupModuleRel($modules, $group_id)
    {
        $groupModuleRel= collect($modules)->map(function($module) use ($group_id) {
            return [
                'group_id' =>$group_id,
                'module_id' =>$module
            ];
        })->toArray();
        GroupModuleRel::insert($groupModuleRel);
    }

    public function createGroup($name,$user_id,$remark)
    {
        $data = [
            'user_id' =>$user_id,
            'name' =>$name,
            'remark' =>$remark,
            'owner_id' =>$user_id
        ];
        $group_id  = Groups::create($data);
        return $group_id;
    }

    public function bindBase($warehouse_id,$group_id)
    {
     $group = Groups::find($group_id);
     $group->warehouse_id = $warehouse_id;
     $group->owner_id =Auth::ownerId();
     $group->save();
     return $group;
    }

}