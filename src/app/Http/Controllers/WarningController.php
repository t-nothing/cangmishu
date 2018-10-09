<?php

namespace App\Http\Controllers;

use App\Mail\ChangeWarningEmail;
use App\Models\Category;
use App\Models\User;
use App\Models\WarehouseEmployee;
use App\Models\UserCategoryWarning;
use App\Models\UserExpirationWarning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class WarningController extends Controller
{
    //@author lym
    //获取商品库存报警配置 列表
    public function stocklist()
    {
        $set_data = User::where('id', app('auth')->guard()->user()->getAuthIdentifier())->first();
        if (empty($set_data)) {
            return formatRet(1, '用户不存在!');
        } else {
            $set_data = [
                'default_warning_stock' => $set_data['default_warning_stock'],
                'warning_email' => empty($set_data['warning_email']) ? '' : $set_data['warning_email'],
            ];
        }
        $warning_data = Category::with('usercategorywarning')->get()->toArray();
        $data['set_data'] = $set_data;
        $data['warning_data'] = $warning_data;
        return formatRet(0, '获取成功!', $data);
    }

    //@author lym
    //保存商品库存报警信息
    public function stockstore(Request $request)
    {
        $this->validate($request, [
            'default_warning_stock' => 'required|integer|min:1',
            'warning_email' => 'required|email|max:255',
            'warning_data' => 'required|array',
            'warning_data.*.category_id' => 'required|integer|min:1',
            'warning_data.*.warning_stock' => 'required|integer|min:1'
        ]);
        $isSendEmail = false;
        $user = User::where('id', app('auth')->guard()->user()->getAuthIdentifier())->first();
        if (empty($user)) {
            return formatRet(1, '用户不存在!');
        }
        $user_id = app('auth')->guard()->user()->getAuthIdentifier();
        $new_email = $request->warning_email;
        $old_email = $user['warning_email'];
        if ($new_email != $old_email) {
            $isSendEmail = true;
        }
        app("db")->beginTransaction();
        if (!User::where('id', $user_id)->update(['default_warning_stock' => $request->default_warning_stock, 'warning_email' => $new_email])) {
            app("db")->rollback();
            return formatRet(1, '保存配置信息失败!');
        }
        foreach ($request->warning_data as $k => $v) {
            if (empty(Category::where('id', $v['category_id'])->first())) {
                app("db")->rollback();
                return formatRet(1, '系统中未找到分类id:' . $v['category_id']);
            }
            $warningInfo = UserCategoryWarning::where('user_id', app('auth')->guard()->user()->getAuthIdentifier())
                ->where('category_id', $v['category_id']);
            if (empty($warningInfo->first())) {
                $UserCategoryWarning = new UserCategoryWarning;
                $UserCategoryWarning->user_id = $user_id;
                $UserCategoryWarning->category_id = $v['category_id'];
                $UserCategoryWarning->warning_stock = $v['warning_stock'];
                if (!$UserCategoryWarning->save()) {
                    app("db")->rollback();
                    return formatRet(2, '添加库存预警失败!');
                }
            } else {
                if (!$warningInfo->update(['warning_stock' => $v['warning_stock']])) {
                    app("db")->rollback();
                    return formatRet(2, '修改库存预警失败!');
                }
            }
        }
        app("db")->commit();
        if ($isSendEmail) {
            $name = $user['name'];
            $wmsUrl = env("WMS_API_URL");
            $sendDate = date("Y-m-d");
            $imageUrl = env("WMS_API_URL");
            $typeName = '库存';
            $currentTime = date("Y年m月d日H:i:s");
            $message = new ChangeWarningEmail($new_email, $name, $wmsUrl, $sendDate, $imageUrl, $typeName, $currentTime, $new_email, $old_email);
            $message->onQueue('emails');
            Mail::send($message);
            if (!empty($old_email)) {
                $message1 = new ChangeWarningEmail($old_email, $name, $wmsUrl, $sendDate, $imageUrl, $typeName, $currentTime, $new_email, $old_email);
                $message1->onQueue('emails');
                Mail::send($message1);
            }
        }
        return formatRet(0, '修改库存预警成功!');
    }

    //@author lym
    //获取商品保质期到期配置 列表
    public function expirationlist()
    {
        $set_data = User::where('id', app('auth')->guard()->user()->getAuthIdentifier())->first();
        if (empty($set_data)) {
            return formatRet(1, '用户不存在!');
        } else {
            $set_data = [
                'default_warning_expiration' => $set_data['default_warning_expiration'],
                'warning_expiration_email' => empty($set_data['warning_expiration_email']) ? '' : $set_data['warning_expiration_email'],
            ];
        }
        $warning_data = Category::with('userexpirationwarning')->get()->toArray();
        $data['set_data'] = $set_data;
        $data['warning_data'] = $warning_data;
        return formatRet(0, '获取成功!', $data);
    }

    //@author lym
    //保存商品保质期到期报警信息
    public function expirationstore(Request $request)
    {
        $this->validate($request, [
            'default_warning_expiration' => 'required|integer|min:1',
            'warning_email' => 'required|email|max:255',
            'warning_data' => 'required|array',
            'warning_data.*.category_id' => 'required|integer|min:1',
            'warning_data.*.warning_expiration' => 'required|integer|min:1'
        ]);
        $isSendEmail = false;
        $user = User::where('id', app('auth')->guard()->user()->getAuthIdentifier())->first();
        if (empty($user)) {
            return formatRet(1, '用户不存在!');
        }
        $user_id = app('auth')->guard()->user()->getAuthIdentifier();
        $new_email = $request->warning_email;
        $old_email = $user['warning_expiration_email'];
        if ($new_email != $old_email) {
            $isSendEmail = true;
        }
        app("db")->beginTransaction();
        if (!User::where('id', $user_id)->update(['default_warning_expiration' => $request->default_warning_expiration, 'warning_expiration_email' => $new_email])) {
            app("db")->rollback();
            return formatRet(1, '保存配置信息失败!');
        }
        foreach ($request->warning_data as $k => $v) {
            if (empty(Category::where('id', $v['category_id'])->first())) {
                app("db")->rollback();
                return formatRet(1, '系统中未找到分类id:' . $v['category_id']);
            }
            $warningInfo = UserExpirationWarning::where('user_id', app('auth')->guard()->user()->getAuthIdentifier())
                ->where('category_id', $v['category_id']);
            if (empty($warningInfo->first())) {
                $UserExpirationWarning = new UserExpirationWarning();
                $UserExpirationWarning->user_id = $user_id;
                $UserExpirationWarning->category_id = $v['category_id'];
                $UserExpirationWarning->warning_expiration = $v['warning_expiration'];
                if (!$UserExpirationWarning->save()) {
                    app("db")->rollback();
                    return formatRet(2, '添加保质期预警失败!');
                }
            } else {
                if (!$warningInfo->update(['warning_expiration' => $v['warning_expiration']])) {
                    app("db")->rollback();
                    return formatRet(2, '修改保质期预警失败!');
                }
            }
        }
        app("db")->commit();
        if ($isSendEmail) {
            $name = $user['name'];
            $wmsUrl = env("WMS_API_URL");
            $sendDate = date("Y-m-d");
            $imageUrl = env("WMS_API_URL");
            $typeName = '保质期';
            $currentTime = date("Y年m月d日H:i:s");
            $message = new ChangeWarningEmail($new_email, $name, $wmsUrl, $sendDate, $imageUrl, $typeName, $currentTime, $new_email, $old_email);
            $message->onQueue('emails');
            Mail::send($message);
            if (!empty($old_email)) {
                $message1 = new ChangeWarningEmail($old_email, $name, $wmsUrl, $sendDate, $imageUrl, $typeName, $currentTime, $new_email, $old_email);
                $message1->onQueue('emails');
                Mail::send($message1);
            }
        }
        return formatRet(0, '修改保质期预警成功!');
    }

}
