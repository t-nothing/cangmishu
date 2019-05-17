<?php
/**
 * Created by PhpStorm.
 * User: NLE-Tech
 * Date: 2019/5/16
 * Time: 11:31
 */

namespace App\Services;

use App\Models\User;
use App\Models\UserGroupRel;
class EmployeeService
{
    public function  getEmployeeList($user_id,$warehouse_id, $page_size =10, $username =null, $group_id =null,$rm =null)
    {
        $user = User::where('boss_id',$user_id)->when($username,function($q) use($username){
            $q->where('nickname', 'like',"%".$username.'%');
        });
        if($rm){ //要派除
            $not =  UserGroupRel::where('group_id',$group_id)->pluck('user_id')->toArray();
            $user->whereNotIn('id',$not);
        }else{ //不用排除
            $user->when($group_id ||$warehouse_id,function ($q)  use ($group_id,$warehouse_id){
                return $q->whereHas('groups',function ($q) use ($group_id,$warehouse_id) {
                    return  $q->when($warehouse_id,function ($qq) use ($warehouse_id){
                        return $qq->where('warehouse_id',$warehouse_id);
                    })
                        ->when($group_id,function($query) use ($group_id){
                            return $query->where('groups.id',$group_id);
                        });
                });
            });
        };
        return $user->with(['groups'])->paginate($page_size)->toArray();
    }
}