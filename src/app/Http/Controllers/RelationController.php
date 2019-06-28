<?php
namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Models\Groups;
use App\Models\User;
use App\Models\UserGroupRel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RelationController extends  Controller
{
        public function  store(BaseRequests $requests)
        {
            $this->validate($requests,[
                'group_id' =>[
                    'required','integer','min:0',
                    Rule::exists('groups','id')->where('user_id',Auth::ownerId())
                ],
                'user_id'  =>[
                    'required','integer','min:0',
                    Rule::exists('user','id')->where('boss_id',Auth::ownerId())
                ]
            ]);
            //判断分组是否有权限等信息
            $group = Groups::find($requests->group_id);
            $modules = $group->modules;

            if(!$group->warehouse_id||!$group->owner_id || !$modules){
                return eRet('请先为分组分配权限');
            }
           //判断员功所在分组是否一致

            $old_group =  UserGroupRel::where('user_id', $requests->user_id)->pluck('group_id')->toArray();

            $old_warehouses = Groups::whereIn('id',$old_group)->pluck('warehouse_id')->toArray();

            $old_warehouses = array_unique($old_warehouses);

            if($old_warehouses){
                if(count($old_warehouses) >1 ){
                    return formatRet(500,"员工已在两个不同仓库，请先将员工从一个分组移除");
                }

                $warehouse_id = $group->warehouse_id;
                if($old_warehouses[0] != $warehouse_id){
                    return formatRet(500,"不可将员工添加至不同仓库的分组");
                }
            }

            $rel = UserGroupRel::where('user_id', $requests->user_id)->where('group_id', $requests->group_id)->first();
            if($rel){
                return formatRet(500,'用户已存在于此分组');
            };
            $rel = new UserGroupRel;
            $rel->user_id  = $requests->user_id;
            $rel->group_id = $requests->group_id;

            User::where('id', $requests->user_id)->update(['default_warehouse_id'=>$group->warehouse_id,'is_activated' =>1]);

            if($rel->save()){
                return formatRet(0);
            }
            return formatRet(500,'添加员工失败');


        }



    public function destroy(BaseRequests $request)
    {
//        dd($request->all(),Auth::ownerId());
        $this->validate($request,[
            'group_id' =>[
                'required','integer','min:0',
                Rule::exists('groups','id')->where('user_id',Auth::ownerId())
            ],
            'user_id'  =>[
                'required','integer','min:0',
                Rule::exists('user','id')->where('boss_id',Auth::ownerId())
            ]
        ]);

        $rel = UserGroupRel::where('user_id', $request->user_id)->where('group_id', $request->group_id)->first();
        if(!$rel){
            return formatRet(500,'未在此分组中找到该员工');
        }

        if($rel->delete()){
            return formatRet(0);
        }else{
            return formatRet(500,'删除失败');
        }

    }

}