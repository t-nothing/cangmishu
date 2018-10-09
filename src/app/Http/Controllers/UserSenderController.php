<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSender;
use App\Rules\PageSize;

class UserSenderController extends Controller
{
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'      => 'integer|min:1',
            'page_size' => new PageSize,
        ]);

        $senders = UserSender::where('user_id', app('auth')->id())->paginate($request->input('page_size'));

        return formatRet(0, '', $senders->toArray());
    }

    /**
     * 详情
     */
    public function show(Request $request, $id)
    {
        if ($id == 'default') {
            return $this->default($request);
        }

        $sender = UserSender::where('user_id', app('auth')->id())
                            ->where('id', $id)
                            ->first();

        if (! $sender) {
            return formatRet(404, '发件人不存在');
        }

        return formatRet(0, '', $sender->toArray());
    }

    /**
     * 默认
     */
    public function default(Request $request)
    {
        $sender = UserSender::where('user_id', app('auth')->id())->where('is_default', 1)->first();

        $data = $sender ? $sender->toArray() : [];

        return formatRet(0, '', $data);
    }

    /**
     * 设置为默认
     */
    public function setDefault(Request $request, $id)
    {
        $user_id = app('auth')->id();

        if (! $sender = UserSender::where('user_id', $user_id)->where('id', $id)->first()) {
            return formatRet(404, '发件人不存在');
        }

        if ($sender->is_default == 1) {
            return formatRet(500, '已设置为默认，请勿重复操作');
        }

        app('db')->transaction(function () use ($request, $user_id, $sender) {
            UserSender::where('user_id', $user_id)->update(['is_default' => 0]);

            $sender->is_default = 1;
            $sender->save();
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

        $sender = new UserSender;
        $sender->user_id    = $user_id;
        $sender->is_default = $request->is_default;
        $sender->country    = $request->country;
        $sender->province   = $request->province;
        $sender->city       = $request->city;
        $sender->door_no    = $request->door_no;
        $sender->address    = $request->address;
        $sender->postcode   = $request->postcode;
        $sender->fullname   = $request->fullname;
        $sender->phone_area_code = $request->phone_area_code;
        $sender->phone      = $request->phone;
        $sender->email      = $request->email;
        $sender->company    = $request->company;
        $sender->remark     = $request->remark;

        app('db')->transaction(function () use ($request, $user_id, $sender) {
            if ($request->is_default == 1) {
                UserSender::where('user_id', $user_id)->update(['is_default' => 0]);
            }

            $sender->save();
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

        if (! $sender = UserSender::where('user_id', $user_id)->where('id', $request->id)->first()) {
            return formatRet(404, '发件人不存在');
        }

        $sender->is_default      = $request->is_default;
        $sender->country         = $request->country;
        $sender->province        = $request->province;
        $sender->city            = $request->city;
        $sender->door_no         = $request->door_no;
        $sender->address         = $request->address;
        $sender->postcode        = $request->postcode;
        $sender->fullname        = $request->fullname;
        $sender->phone_area_code = $request->phone_area_code;
        $sender->phone           = $request->phone;
        $sender->email           = $request->email;
        $sender->company         = $request->company;
        $sender->remark          = $request->remark;

        app('db')->transaction(function () use ($request, $user_id, $sender) {
            if ($request->is_default == 1) {
                UserSender::where('user_id', $user_id)->update(['is_default' => 0]);
            }

            UserSender::where('id', $request->id)->update(['is_default' => $request->is_default]);

            $sender->save();
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

        if (! $sender = UserSender::where('user_id', $user_id)->where('id', $request->id)->first()) {
            return formatRet(404, '发件人不存在');
        }

        if (! $sender->delete()) {
            return formatRet(500, '删除失败');
        }

        return formatRet(0);
    }
}
