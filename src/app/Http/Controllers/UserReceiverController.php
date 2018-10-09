<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserReceiver;
use App\Rules\PageSize;

class UserReceiverController extends Controller
{
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'      => 'integer|min:1',
            'page_size' => new PageSize,
        ]);

        $receivers = UserReceiver::where('user_id', app('auth')->id())->paginate($request->input('page_size'));

        return formatRet(0, '', $receivers->toArray());
    }

    /**
     * 详情
     */
    public function show(Request $request, $id)
    {
        if ($id == 'default') {
            return $this->default($request);
        }

        $receiver = UserReceiver::where('user_id', app('auth')->id())
                            ->where('id', $id)
                            ->first();

        if (! $receiver) {
            return formatRet(404, '收件人不存在');
        }

        return formatRet(0, '', $receiver->toArray());
    }

    /**
     * 默认
     */
    public function default(Request $request)
    {
        $receiver = UserReceiver::where('user_id', app('auth')->id())->where('is_default', 1)->first();

        $data = $receiver ? $receiver->toArray() : [];

        return formatRet(0, '', $data);
    }

    /**
     * 设置为默认
     */
    public function setDefault(Request $request, $id)
    {
        $user_id = app('auth')->id();

        if (! $receiver = UserReceiver::where('user_id', $user_id)->where('id', $id)->first()) {
            return formatRet(404, '收件人不存在');
        }

        if ($receiver->is_default == 1) {
            return formatRet(500, '已设置为默认，请勿重复操作');
        }

        app('db')->transaction(function () use ($request, $user_id, $receiver) {
            UserReceiver::where('user_id', $user_id)->update(['is_default' => 0]);

            $receiver->is_default = 1;
            $receiver->save();
        });

        return formatRet(0);
    }

    /**
     * 添加
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'is_default' => 'required|boolean',
            'country'    => 'required|string',
            'province'   => 'present|required_if:country,CN|string',
            'city'       => 'required|string',
            'door_no'    => 'required|string',
            'address'    => 'required|string',
            'postcode'   => 'required|string',
            'fullname'   => 'required|string',
            'phone_area_code' => 'required|in:0031,0049,0032',
            'phone'      => 'required|string',
            'email'      => 'present|string',
            'company'    => 'present|string',
            'remark'     => 'present|string',
        ]);

        $user_id = app('auth')->id();

        $receiver = new UserReceiver;
        $receiver->user_id    = $user_id;
        $receiver->is_default = $request->is_default;
        $receiver->country    = $request->country;
        $receiver->province   = $request->province;
        $receiver->city       = $request->city;
        $receiver->door_no    = $request->door_no;
        $receiver->address    = $request->address;
        $receiver->postcode   = $request->postcode;
        $receiver->fullname   = $request->fullname;
        $receiver->phone_area_code = $request->phone_area_code;
        $receiver->phone      = $request->phone;
        $receiver->email      = $request->email;
        $receiver->company    = $request->company;
        $receiver->remark     = $request->remark;

        app('db')->transaction(function () use ($request, $user_id, $receiver) {
            if ($request->is_default == 1) {
                UserReceiver::where('user_id', $user_id)->update(['is_default' => 0]);
            }

            $receiver->save();
        });

        return formatRet(0);
    }

    /**
     * 编辑
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'id'         => 'required|integer|min:1',
            'is_default' => 'required|boolean',
            'country'    => 'required|string',
            'province'   => 'present|required_if:country,CN|string',
            'city'       => 'required|string',
            'door_no'    => 'required|string',
            'address'    => 'required|string',
            'postcode'   => 'required|string',
            'fullname'   => 'required|string',
            'phone_area_code' => 'required|in:0031,0049,0032',
            'phone'      => 'required|string',
            'email'      => 'present|string',
            'company'    => 'present|string',
            'remark'     => 'present|string',
        ]);

        $user_id = app('auth')->id();

        if (! $receiver = UserReceiver::where('user_id', $user_id)->where('id', $request->id)->first()) {
            return formatRet(404, '收件人不存在');
        }

        $receiver->country         = $request->country;
        $receiver->province        = $request->province;
        $receiver->city            = $request->city;
        $receiver->door_no         = $request->door_no;
        $receiver->address         = $request->address;
        $receiver->postcode        = $request->postcode;
        $receiver->fullname        = $request->fullname;
        $receiver->phone_area_code = $request->phone_area_code;
        $receiver->phone           = $request->phone;
        $receiver->email           = $request->email;
        $receiver->company         = $request->company;
        $receiver->remark          = $request->remark;

        app('db')->transaction(function () use ($request, $user_id, $receiver) {
            if ($request->is_default == 1) {
                UserReceiver::where('user_id', $user_id)->update(['is_default' => 0]);
            }

            UserReceiver::where('id', $request->id)->update(['is_default' => $request->is_default]);
            
            $receiver->save();
        });

        return formatRet(0);
    }

    /**
     * 删除
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer|min:1',
        ]);

        $user_id = app('auth')->id();

        if (! $receiver = UserReceiver::where('user_id', $user_id)->where('id', $request->id)->first()) {
            return formatRet(404, '收件人不存在');
        }

        if (! $receiver->delete()) {
            return formatRet(500, '删除失败');
        }

        return formatRet(0);
    }
}
