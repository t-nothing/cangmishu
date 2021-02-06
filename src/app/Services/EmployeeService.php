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

use App\Models\User;
use App\Models\UserGroupRel;
use Illuminate\Support\Facades\DB;

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
            $user->when($group_id ||$warehouse_id,function ($qqu)  use ($group_id,$warehouse_id){
                return $qqu->whereHas('groups',function ($qu) use ($group_id,$warehouse_id) {
                    return  $qu->when($warehouse_id,function ($qq) use ($warehouse_id){
                        return $qq->where('warehouse_id',$warehouse_id);
                    })
                        ->when($group_id,function($query) use ($group_id){
                            return $query->where('groups.id',$group_id);
                        });
                });
            });
        };
       return  $user->with(['groups'])->paginate($page_size)->toArray();
    }
}