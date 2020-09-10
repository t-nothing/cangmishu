<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateReceiverAddressRequest;
use App\Http\Requests\UpdateReceiverAddressRequest;
use App\Models\ReceiverAddress;

use Illuminate\Support\Facades\Auth;

class ReceiverAddressController extends Controller
{
    /**
     * 收件人列表
     */
    public function index(BaseRequests $request)
    {
        $owner_id= Auth::ownerId();
        $address= ReceiverAddress::where('owner_id',$owner_id)->paginate($request->input('page_size',10));
        foreach ($address as $add){
            $add->append('full_address');
        }
        return  formatRet(0,'',$address->toArray());
    }

    public  function store(CreateReceiverAddressRequest $request)
    {
        app('log')->info('添加收件人地址',$request->all());
        $user_id = Auth::ownerId();
        try{
            $data = $request->all();
            $data = array_merge($data,['owner_id' =>$user_id]);
            ReceiverAddress::create($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->info('仓秘书添加收件人地址失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.receiverAddFailed"));
        }
    }

    public function update(UpdateReceiverAddressRequest $request, $address_id)
    {
        app('log')->info('编辑收件人地址',$request->all());
        try{
            $data = $request->all();
            ReceiverAddress::where('id',$address_id)->update($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->info('仓秘书编辑收件人地址失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.receiverUpdateFailed"));
        }
    }

    public function destroy($address_id)
    {
        app('log')->info('删除收件人地址',['id'=>$address_id]);
        $address = ReceiverAddress::find($address_id);
        if(!$address){
            return formatRet(500, trans("message.receiverNotExist"));
        }
        if ($address->owner_id != Auth::ownerId()){
            return formatRet(500,  trans("message.noPermission"));
        }
        try{
            $address->delete();
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->info('仓秘书删除收件人地址失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.receiverDeleteFailed"));
        }
    }

    public function  show(BaseRequests $request, $address_id)
    {

        app('log')->info('查看收件人地址', ['id' => $address_id]);

        $address = ReceiverAddress::where('owner_id',Auth::ownerId())->find($address_id);
        if(!$address){
            return formatRet(500, trans("message.receiverNotExist"));
        }
        return formatRet(0,"",$address->toArray());
    }
}
