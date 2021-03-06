<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
use App\Models\Product;
use App\Models\ProductSpec;
use Illuminate\Support\Facades\Mail;
use App\Jobs\Sms;


class UserService
{

    function quickRegister(Request $request)
    {

        #判断是不是这个值

        $user = new User;
        # 注册
        app('db')->beginTransaction();
        try {

            $type           = $request->type;
            $code           = $request->code;
            $mobile         = $request->mobile;
            $email          = $request->email;

            if($type === "mobile" && empty($email))
            {
                $email = sprintf("%s@cangmishu.com", $mobile);
            }

            #先创建一个用户
            $user->email                = $email;
            $user->phone                = $mobile??'';
            $user->password             = Hash::make($request->password);
            $nickname                   = explode("@",$email);
            $user->nickname             = $request->nickname??$nickname[0];
            $user->avatar               = $request->avatar??env("APP_URL")."/images/default_avatar.png";
            $user->wechat_openid        = $request->wechat_openid ?? null;
            $user->wechat_mini_program_open_id   = $request->wechat_mini_program_open_id ?? null;
            $user->union_id = $request->union_id ?? null;
            $user->app_openid = $request->app_openid ?? null;
            $user->save();

            $user->setActivated();

            #创建一个默认仓库
            $warehouse                  = new Warehouse();
            $warehouse->owner_id        = $user->id;
            $warehouse->name_cn         = empty($request->warehouse_name)?'我的仓库':$request->warehouse_name;

            // $initCode = Cache::increment('WAREHOUSE_CODE') + 1000;
            $warehouse->code            = Warehouse::no($user->id);
            $warehouse->type            = Warehouse::TYPE_PERSONAL;
            $warehouse->area            = $request->warehouse_area??200;
            $warehouse->contact_email   = $email;
            $warehouse->status          = true;
            $warehouse->apply_num       = 1;
            $warehouse->operator        = $user->id;
            $warehouse->contact_user    = $request->nickname??'';
            $warehouse->contact_number  = "";
            $warehouse->country         = $request->country??"";
            $warehouse->city            = $request->city??"";

            // 仓库被创建时，如果是公共，则无使用者；如果是私有，则是创建者
            $warehouse->is_used         = 1;
            $warehouse->user_id         = $user->id;

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
            $feature->owner_id         =  $user->id;

            if (! $feature->save()) {
                throw new \Exception("默认特性创建失败", 1);
            }

            #添加默认货区

            $warehouseArea = new WarehouseArea;
            $warehouseArea->warehouse_id = $warehouse->id;
            $warehouseArea->warehouse_feature_id = $feature->id;
            $warehouseArea->owner_id     =  $user->id;
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
            $location->code              = "默认货位";
            $location->capacity          = 100;
            $location->is_enabled        = 1;
            $location->passage           = 2;
            $location->row               = 2;
            $location->col               = 2;
            $location->floor             = 2;
            $location->remark            = "自动创建货位";
            $location->owner_id         =  $user->id;

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
            $batchType->name         = "默认入库单分类";
            $batchType->is_enabled   = 1;
            $batchType->area_id      = $warehouseArea->id;
            $batchType->owner_id      = $user->id;
            if (!$batchType->save()) {
                throw new \Exception("默认入库单分类创建失败", 1);
            }

            #添加默认出单库
            $orderType = new OrderType;

            $orderType->warehouse_id = $warehouse->id;
            $orderType->name         = "默认出库单分类";
            $orderType->is_enabled   = 1;
            $orderType->is_partial   = 2;
            $orderType->area_id      = $warehouseArea->id;
            $orderType->owner_id =  $user->id;

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
            $category->owner_id                     = $user->id;
//            $category->need_expiration_date         = 0;
//            $category->need_production_batch_number = 0;
//            $category->need_best_before_date        = 0;
            if (!$category->save()) {
                throw new \Exception("默认货品分类创建失败", 1);
            }

            // $userCategory = new  UserCategoryWarning();
            // $userCategoryData = [
            //     'user_id' => $user->id,
            //     'category_id' => $category->id,
            //     'warning_stock'  => env("DEFAULT_WARNING_STOCK",50)
            // ];
            // $user->default_warning_stock = env("DEFAULT_WARNING_STOCK",50);
            // $user->save();

            // //用户分类预警新增
            // $userCategory::create($userCategoryData);

            #添加供应商
            $distributor = new Distributor;
            $distributor->user_id = $user->id;
            $distributor->name_cn = "默认供应商";
            $distributor->country = "中国";
            $distributor->name_en = "Default Supplier";
//            $distributor->phone = " ";
//            $distributor->email = " ";
//            $distributor->country = " ";
//            $distributor->city = " ";
//            $distributor->street = " ";

            if (!$distributor->save()) {
                throw new \Exception("默认供应商创建失败", 1);
            }


            $product = new Product;
            $product->category_id         = $category->id;
            $product->name_cn             = "我的第一个商品";
            $product->name_en             = "我的第一个商品";
            $product->hs_code             = "";
            $product->barcode             = "123456";
            $product->origin              = "中国";
            $product->display_link        = "";
            $product->remark              = $request->input('remark', '');
            $product->photos              = "https://api.cangmishu.com/storage/imgs/h9AeqOsyWvOnMJ0jrWdgKALbO8uahfXXKWwUWHvL.jpeg";
            $product->owner_id            = $user->id;
            $product->warehouse_id        = $warehouse->id;
            $product->sale_price          = 100;
            $product->purchase_price      = 80;

            $specs[] = [
                'product_id'     => 0,
                'name_cn'        => "规格一",
                'name_en'        => "规格一",
                'net_weight'     => "800",
                'gross_weight'   => "800",
                'relevance_code' => "myskucode",
                'sale_price'     => 100,
                'purchase_price' => 80,
                'owner_id'       => $user->id,
                'warehouse_id'   => $warehouse->id,
                'is_warning'     => 1
            ];

            $product->save();
            foreach ($specs as $k => $v) {
                $specs[$k]['product_id']    = $product->id;
            }
            ProductSpec::insert($specs);

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            throw new \Exception("系统保存数据失败:".$e->getMessage(), 1);
        }

        return $user;
    }

    public function getRandCode(){
        $chars = '0123456789';
        mt_srand((double) microtime() * 1000000 * getmypid());
        $CheckCode = "";
        while (strlen($CheckCode) < 6) {
            $CheckCode .= substr($chars, (mt_rand() % strlen($chars)), 1);
        }

        return $CheckCode;
    }

    /**
     * @param $code
     * @param $email
     */
    public function createUserEmailVerifyCode($code ,$email)
    {
        VerifyCode::updateOrCreate(['email' => $email], ['code' => $code, 'expired_at' => time() + 5 * 60]);

        $logo = config('app.url') . "/images/logo.png";
        $qrCode = config('app.url') . "/images/qrCode.png";

        $message = new VerifyCodeEmail($code, $logo, $qrCode);
        $message->onQueue('cangmishu_emails');

        Mail::to($email)->send($message);
    }

    /**
     * @param $code
     * @param $mobile
     */
    public function createUserSMSVerifyCode($code ,$mobile)
    {
        VerifyCode::updateOrCreate(
            ['email' => $mobile],
            ['code' => $code, 'expired_at' => time() + 5 * 60]
        );

        Sms::dispatch('register', $mobile, $code)->onQueue('cangmishu_push');
    }
}
