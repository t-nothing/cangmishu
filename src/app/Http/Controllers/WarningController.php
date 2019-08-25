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
        $warning_data = Category::where('owner_id',Auth::ownerId())->select(['id','name_cn','name_en','warning_stock'])->get();
        return formatRet(0,'',compact('warning_email','warning_data','default_warning_stock'));
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
            'warning_data.*.warning_stock' => 'required|integer|min:1|max:1000'
        ]);
        $isSendEmail = false;
        $user = User::where('id', app('auth')->ownerId())->first();
        if (empty($user)) {
            return formatRet(500, '用户不存在!');
        }
        $user_id = app('auth')->ownerId();
        $new_email = $request->input('warning_email');
        $old_email = $user['warning_email'];

        app("db")->beginTransaction();
        try{
            $user->update(
                [
                    'default_warning_stock' => $request->input('default_warning_stock'), 
                    'warning_email' => $new_email
                ]
            );
            foreach ($request->warning_data as $k => $v) {

                $category = Category::find('id', $v['category_id']);
                if(!$category) {
                    throw new \Exception("分类ID未找到", 1);
                    
                }
                if($category->owner_id != app('auth')->ownerId())
                {
                    throw new \Exception("非法请求,此分类不属于你", 1);
                }

                $category->warning_stock = $v['warning_stock'];
                $category->save();
            }
            app("db")->commit();
        }catch(\Exception $ex) {
            app("db")->rollback();
            return formatRet(1, $ex->getMessage());
        }
        
        return formatRet(0, '修改库存预警成功!');
    }

    public  function destroy(BaseRequests $request)
    {
        return formatRet(0, '不支持删除预警!');
    }

}