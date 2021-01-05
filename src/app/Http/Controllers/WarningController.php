<?php
namespace App\Http\Controllers;


use App\Http\Requests\BaseRequests;
use App\Mail\ChangeWarningEmail;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\UserCategoryWarning;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WarningController extends  Controller
{
    public function show(BaseRequests $request)
    {
        // $this->validate($request, [
        //     'warehouse_id' => 'required|int|min:1',
        // ]);

        $warehouse = Warehouse::where('owner_id',Auth::ownerId())->where('id', app('auth')->warehouse()->id)->select('warning_email')->first();
        if(!$warehouse) {
            return formatRet(500, trans("message.warehouseNotExist"));
        }
        $warning_email = $warehouse->warning_email;
        $default_warning_stock =  Auth::user()->default_warning_stock;
        $warning_data = Category::where('owner_id',Auth::ownerId())->where('warehouse_id', app('auth')->warehouse()->id)->select(['id','name_cn','name_en','warning_stock'])->get();
        return formatRet(0,'',compact('warning_email','warning_data','default_warning_stock'));
    }

    //@author lym
    //保存商品库存报警信息
    public function store(BaseRequests $request)
    {
        $this->validate($request, [
            // 'warehouse_id' => 'required|int|min:1',
            'warning_email' => 'required|email|max:255',
            'warning_data' => 'required|array',
            'warning_data.*.category_id' => 'required|integer|min:1',
            'warning_data.*.warning_stock' => 'required|integer|min:1|max:1000'
        ]);


        $isSendEmail = false;
        $warehouse = Warehouse::where('owner_id',Auth::ownerId())->where('id', app('auth')->warehouse()->id)->select('warning_email')->first();
        if(!$warehouse) {
            return formatRet(500, trans("message.warehouseNotExist"));
        }
        $user_id = app('auth')->ownerId();
        $new_email = $request->warning_email;

        app("db")->beginTransaction();
        try{
            Warehouse::where('owner_id',Auth::ownerId())->where('id', app('auth')->warehouse()->id)->update(
                [
                    'warning_email' => $new_email
                ]
            );
            foreach ($request->warning_data as $k => $v) {
                $category = Category::find( $v['category_id']);
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
            app("log")->error($ex->getMessage());
            return formatRet(500, trans("message.failed"));
        }
        
        return formatRet(0, trans("message.success"));
    }

    public  function destroy(BaseRequests $request)
    {
        return formatRet(0, trans("message.warehouseWarningDeleteFailed"));
    }

}