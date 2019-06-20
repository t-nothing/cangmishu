<?php

namespace App\Services;


use App\Mail\VerifyCodeEmail;
use App\Models\VerifyCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseFeature;
use App\Models\WarehouseArea;
use App\Models\WarehouseLocation;
use App\Models\BatchType;
use App\Models\Category;
use App\Models\UserCategoryWarning;
use App\Models\OrderType;
use App\Models\Distributor;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;


class UserService{
    
    function quickRegister(Request $request){

        #判断是不是这个值

        $user = new User;
        # 注册
        app('db')->beginTransaction();
        try {

            #先创建一个用户

            $user->email    = $request->email;
            $user->password = Hash::make($request->password);
            $nickname = explode("@",$request->email);
            $user->nickname = $nickname[0];
            $user->avatar = asset("/images/default_avatar.png");
            $user->save();

            $user->setActivated();

            #创建一个默认仓库
            $warehouse = new Warehouse();
            $warehouse->owner_id = $user->id;
            $warehouse->name_cn = $request->warehouse_name;

            $initCode = Cache::increment('WAREHOUSE_CODE') + 1000;
            $warehouse->code = encodeseq($initCode);
            $warehouse->type = Warehouse::TYPE_PERSONAL;
            $warehouse->area = $request->warehouse_area;
            $warehouse->contact_email = $request->email;
            $warehouse->status = true;
            $warehouse->apply_num = 0;
            $warehouse->operator = $user->id;
            $warehouse->contact_user = $user->id;
            $warehouse->contact_number = "";

            // 仓库被创建时，如果是公共，则无使用者；如果是私有，则是创建者
            $warehouse->is_used = 1;
            $warehouse->user_id = $user->id;
            


            if (!$warehouse->save()) {
                throw new \Exception("创建仓库失败", 1);
            }

            $user->default_warehouse_id = $warehouse->id;
            $user->save();
//            //添加仓库权限
//            if( ! $warehouse::addEmployee(WarehouseEmployee::ROLE_OWNER,$warehouse->owner_id,$warehouse->id, $warehouse->owner_id)){
//                throw new \Exception("给用户添加仓库权限失败", 1);
//            }

            #添加默认特性

            $feature = new WarehouseFeature;

            $feature->warehouse_id = $warehouse->id;
            $feature->name_cn      = "默认特性";
            $feature->name_en      = "Default Feature";
            $feature->is_enabled   = 1;
            $feature->logo         = "";
            $feature->remark       = "自动创建特性";

            if (! $feature->save()) {
                throw new \Exception("默认特性创建失败", 1);
            }

            #添加默认货区

            $warehouseArea = new WarehouseArea;
            $warehouseArea->warehouse_id = $warehouse->id;
            $warehouseArea->warehouse_feature_id = $feature->id;
            $warehouseArea->code         = "AREA";
            $warehouseArea->name_cn      = "默认货区";
            $warehouseArea->name_en      = "Default Area";
            $warehouseArea->is_enabled   = 1;
            $warehouseArea->functions    = [];
            $warehouseArea->remark       = "自动创建货区";

            if (!$warehouseArea->save()) {
                throw new \Exception("默认货区创建失败", 1);
            }

            #添加默认货位

            $location = new WarehouseLocation;

            $location->warehouse_id      = $warehouse->id;
            $location->warehouse_area_id = $warehouseArea->id;
            $location->code              = "LOCATION";
            $location->capacity          = 100;
            $location->is_enabled        = 1;
            $location->passage           = 2;
            $location->row               = 2;
            $location->col               = 2;
            $location->floor             = 2;
            $location->remark            = "自动创建货位";

            if (! $location->save()) {
                throw new \Exception("默认货位创建失败", 1);
            }

//            #添加默认篮筐
//
//            $kep = new Kep;
//
//            $kep->warehouse_id = $warehouse->id;
//            $kep->code         = "KEP";
//            $kep->capacity     = 100;
//            $kep->weight       = 100;
//            $kep->is_enabled   = 1;
//
//            if (!$kep->save()) {
//                throw new \Exception("默认篮筐创建失败", 1);
//            }

            #添加默认入库单

            $batchType = new BatchType;

            $batchType->warehouse_id = $warehouse->id;
            $batchType->name         = "默认分类";
            $batchType->is_enabled   = 1;
            $batchType->area_id      = $warehouseArea->id;
            if (!$batchType->save()) {
                throw new \Exception("默认入库单分类创建失败", 1);
            }

            #添加默认出单库
            $orderType = new OrderType;

            $orderType->warehouse_id = $warehouse->id;
            $orderType->name         = "默认出库单";
            $orderType->is_enabled   = 1;
            $orderType->is_partial   = 2;
            $orderType->area_id      = $warehouseArea->id;

            if (!$orderType->save()) {
                throw new \Exception("默认出库单分类创建失败", 1);
            }


            #添加默认货品分类

            $category = new Category;
            $category->warehouse_id                 = $warehouse->id;
            $category->warehouse_feature_id         = $feature->id;
            $category->name_cn                      = "默认分类";
            $category->name_en                      = "Default Category";
            $category->is_enabled                   = 1;
//            $category->need_expiration_date         = 0;
//            $category->need_production_batch_number = 0;
//            $category->need_best_before_date        = 0;
            if (!$category->save()) {
                throw new \Exception("默认货品分类创建失败", 1);
            }
       
            $userCategory = new  UserCategoryWarning();
            $userCategoryData = [
                'user_id' => $user->id,
                'category_id' => $category->id,
                'warning_stock'  => env("DEFAULT_WARNING_STOCK",50)
            ];
            $user->default_warning_stock = env("DEFAULT_WARNING_STOCK",50);
            $user->save();

            //用户分类预警新增
            $userCategory::create($userCategoryData);

            #添加供应商
            $distributor = new Distributor;
            $distributor->user_id = $user->id;
            $distributor->name_cn = "默认供应商";
            $distributor->name_en = "Default Supplier";
//            $distributor->phone = " ";
//            $distributor->email = " ";
//            $distributor->country = " ";
//            $distributor->city = " ";
//            $distributor->street = " ";

            if (!$distributor->save()) {
                throw new \Exception("默认供应商创建失败", 1);
            }

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            throw new \Exception("系统保存数据失败:".$e->getMessage(), 1);
        }

        return $user;
    }

    public function getCode(){
        $chars='0123456789';
        mt_srand((double)microtime()*1000000*getmypid());
        $CheckCode="";
        while(strlen($CheckCode)<6)
            $CheckCode.=substr($chars,(mt_rand()%strlen($chars)),1);
        return $CheckCode;
    }
    public function createUserVerifyCode($code ,$email)
    {
        VerifyCode::updateOrCreate(['email' => $email], ['code' => $code,'expired_at'=>time()+5*60]);
        $message = new VerifyCodeEmail($code);
         Mail::to($email)->send($message);

    }
}