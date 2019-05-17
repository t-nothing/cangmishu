<?php
namespace App\Http\Controllers;


use App\Http\Requests\BaseRequests;
use App\Mail\ChangeWarningEmail;
use App\Models\Category;
use App\Models\User;
use App\Models\UserCategoryWarning;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WarningController extends  Controller
{
    public function show()
    {
        $warning_email = Auth::user()->warning_email;
        $default_warning_stock =  Auth::user()->default_warning_stock;
        $warning_data = UserCategoryWarning::with(['category'])->where('user_id',Auth::ownerId())->get();
        return formatRet(0,'',compact('warning_email','default_warning_stock','warning_data'));
    }

    //@author lym
    //保存商品库存报警信息
    public function store(BaseRequests $request)
    {
        $this->validate($request, [
            'default_warning_stock' => 'required|integer|min:1',
            'warning_email' => 'required|email|max:255',
            'warning_data' => 'required|array',
            'warning_data.*.category_id' => 'required|integer|min:1',
            'warning_data.*.warning_stock' => 'required|integer|min:1'
        ]);
        $isSendEmail = false;
        $user = User::where('id', app('auth')->ownerId())->first();
        if (empty($user)) {
            return formatRet(500, '用户不存在!');
        }
        $user_id = app('auth')->ownerId();
        $new_email = $request->input('warning_email');
        $old_email = $user['warning_email'];
        if ($new_email != $old_email) {
            $isSendEmail = true;
        }
        app("db")->beginTransaction();
        if (!User::where('id', $user_id)->update(['default_warning_stock' => $request->input('default_warning_stock'), 'warning_email' => $new_email])) {
            app("db")->rollback();
            return formatRet(1, '保存配置信息失败!');
        }
        foreach ($request->warning_data as $k => $v) {
            if (empty(Category::where('id', $v['category_id'])->first())) {
                app("db")->rollback();
                return formatRet(1, '系统中未找到分类id:' . $v['category_id']);
            }
            $warningInfo = UserCategoryWarning::where('user_id', app('auth')->ownerId())
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

    public  function destroy(BaseRequests $request)
    {
        $this->validate($request, [
           'category_id' => 'required|integer|min:1',
        ]);
        if (empty(Category::where('id', $request->category_id)->where('owner_id',Auth::ownerId())->first())) {
            app("db")->rollback();
            return formatRet(1, '系统中未找到分类id:' .$request->category_id);
        }
        UserCategoryWarning::where('user_id',Auth::ownerId())->where('category_id',$request->category_id)->forceDelete();
        return formatRet(0);
    }

}