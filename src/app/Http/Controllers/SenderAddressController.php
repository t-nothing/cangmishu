<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers;


use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateSenderAddressRequest;
use App\Http\Requests\UpdateSenderAddressRequest;
use App\Models\SenderAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SenderAddressController extends  Controller
{
    public function index(BaseRequests $request)
    {
        $owner_id= Auth::ownerId();
        $address = SenderAddress::where('owner_id',$owner_id)->paginate($request->input('page_size',10));
        foreach ($address as $add){
            $add->append('full_address');
        }
        return  formatRet(0,'',$address->toArray());
    }


    public  function store(CreateSenderAddressRequest $request)
    {
        app('log')->info('添加发件人地址',$request->all());
        $user_id = Auth::ownerId();

        DB::beginTransaction();
        try{
            $data = $request->all();
            $data = array_merge($data,['owner_id' =>$user_id]);
            app('log')->info("data",$data);
            $s = SenderAddress::create($data);
            app('log')->info('发件人地址',$s->toArray());
            DB::commit();
            return formatRet(0);
        }catch (\Exception $e){
            DB::rollBack();
            app('log')->info('仓秘书添加发件人地址失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.senderAddFailed"));
        }
    }

    public function update(UpdateSenderAddressRequest $request,$address_id)
    {
        app('log')->info('编辑发件人地址',$request->all());
        DB::beginTransaction();
        try{
            $data = $request->all();
            SenderAddress::where('id',$address_id)->update($data);
            DB::commit();
            return formatRet(0);
        }catch (\Exception $e){
            DB::rollBack();
            app('log')->info('仓秘书编辑发件人地址失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.senderUpdateFailed"));
        }
    }

    public function destroy($address_id)
    {
        app('log')->info('删除发件人地址',['id'=>$address_id]);

        $address = SenderAddress::find($address_id);
        if(!$address){
            return formatRet(500, trans("message.senderNotExist"));
        }
        if ($address->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }
        try{
            $address->delete();
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->info('仓秘书删除发件人地址失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.senderDeleteFailed"));
        }
    }

    public function  show(BaseRequests $request, $address_id)
    {
        app('log')->info('查看收件人地址', ['id' => $address_id]);
        $address = SenderAddress::where('owner_id',Auth::ownerId())->find($address_id);
        if(!$address){
            return formatRet(500, trans("message.senderNotExist"));
        }
        return formatRet(0,"",$address->toArray());
    }
}