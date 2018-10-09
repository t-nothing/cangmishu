<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Rules\PageSize;
use App\Models\LeaseApplicationInfo;
use App\Models\Warehouse;
use App\Models\UserCertificationOwner;
use App\Models\UserCertificationRenters;
use App\Models\UserExtra;
use App\Models\WarehouseEmployee;

class LeaseApplicationInfoController extends Controller
{
    //租赁方 查看可申请仓库列表 ok
    public function list(Request $request)
    {
        $this->validate($request, [
            'country' => 'string|max:255', //国家
            'area_min' => 'numeric', //面积最小值
            'area_max' => 'numeric', //面积最大值
            'keywords' => 'string|max:255', //关键词
            'page' => 'integer|min:1',
            'page_size' => new PageSize,
        ]);

        $user_id = Auth::id();

        $warehouse_ids = WarehouseEmployee::where('user_id', $user_id)
            ->where('role_id', WarehouseEmployee::ROLE_RENTER)
            ->get()
            ->pluck('warehouse_id');

        $warehouse = Warehouse::where('type', Warehouse::TYPE_OPEN)//共享仓库
            ->where('status', 1)// 启用状态
            // ->where('is_used', 0)// 没有使用
            ->where('owner_id', '!=', $user_id)// 避免产权方是租赁者
            ->whereNotIn('id', $warehouse_ids);

        if ($request->filled('country')) {
            $warehouse->where('country', $request->country);
        }

        if ($request->filled('area_min')) {
            $warehouse->where('area', '>=', $request->area_min);
        }

        if ($request->filled('area_max')) {
            $warehouse->where('area', '<=', $request->area_max);
        }

        if ($request->filled('keywords')) {
            $warehouse->hasKeyword($request->keywords);
        }

        $warehouses = $warehouse->paginate($request->input('page_size'));

        return formatRet(0, '', $warehouses->toArray());
    }

    //租赁方 保存租赁方申请详情 ok 需要修改对应仓库申请数量
    public function rentersCreate(Request $request)
    {
        $this->validate($request, [
            'user_account' => 'required|email', //用户账号
            'warehouse_id' => 'required|integer|min:1', //仓库id
            'warehouse_name' => 'required|string|max:255', //仓库名称
            //    'application_data' => 'required|date:Y-m-d', //申请日期
            'application_name' => 'required|string|max:255', //申请人姓名
            'application_phone' => 'required|integer|min:1',//申请人电话
            'application_email' => 'required|email',//申请人邮箱
            'weekly_shipments' => 'required|integer|min:1', //每周发货量
            'weekly_weight' => 'required|integer|min:1',//每周发货重量
            'goods_name' => 'required|string|max:255',//商品名
            'sell_country' => 'required|string|max:255', //销售国家
            'sales_mode' => 'required|string|max:255',//销售方式
        ]);

        $user = app('auth')->user();

        if (!$user->extra || $user->extra->is_certificated_renter == 0) {
            return formatRet(403, '请先通过租赁仓库的认证');
        }

        //待审核 已通过
        $rentApplicationCount = LeaseApplicationInfo::where('owner_id', $user->id)
            ->where('lease_status','!=', LeaseApplicationInfo::AUDIT_REJECT)
            ->count();

        if ($rentApplicationCount >= $user->extra->rent_limit) {
            return formatRet(500, '申请数不能超过可租赁仓库上限');
        }

        if (!$warehouse = Warehouse::where('id', $request->warehouse_id)->where('type',
            Warehouse::TYPE_OPEN)->first()) {
            return formatRet(1, '此仓库不可申请');
        }
        //一个用户只能向一个仓库提交一次申请
        $owner_id = app('auth')->guard()->user()->getAuthIdentifier();

        $exits = LeaseApplicationInfo::where('owner_id', $owner_id)
            ->where('warehouse_id', $request->warehouse_id)
            ->where(function ($query) {
                $query->where('lease_status', LeaseApplicationInfo::AUDIT_PENDING)
                    ->orWhere('lease_status', LeaseApplicationInfo::AUDIT_PASSED);
            })
            ->first();
        if ($exits) {
            return formatRet(1, '不能重复提交申请', $exits['lease_status']);
        }
        $application = new LeaseApplicationInfo();
        $application->user_account = $request->user_account;
        $application->warehouse_id = $request->warehouse_id;
        $application->warehouse_name = $request->warehouse_name;
        $application->application_data = time();
        $application->application_name = $request->application_name;
        $application->application_phone = $request->application_phone;
        $application->application_email = $request->application_email;
        $application->weekly_shipments = $request->weekly_shipments;
        $application->weekly_weight = $request->weekly_weight;
        $application->goods_name = $request->goods_name;
        $application->sell_country = $request->sell_country;
        $application->sales_mode = $request->sales_mode;
        $application->owner_id = $owner_id;
        $application->lease_status = 1;
        app("db")->beginTransaction();
        if (!$application->save()) {
            app("db")->rollback();
            return formatRet(1, '新增失败');
        }
        Warehouse::where('id', $request->warehouse_id)
            ->increment('apply_num', 1);
        if (!$warehouse->save()) {
            return formatRet(1, '新增失败');
        }
        app("db")->commit();
        app("log")->info("仓库租赁申请信息：" . json_encode($application));
        return formatRet(0, '新增成功');

    }

    //租赁方查看申请列表 ok
    public function rentersApplyList(Request $request)
    {
        $this->validate($request, [
            'status' => 'integer',
            'page' => 'integer|min:1',
            'page_size' => new PageSize,
        ]);
        $owner_id = app('auth')->guard()->user()->getAuthIdentifier();
        $warehouse = Warehouse::getIns()->from('warehouse as w')->join('lease_application_info as a', 'w.id', '=',
            'a.warehouse_id')
            ->where('a.owner_id', '=', $owner_id);
        if ($request->status) {
            $warehouse = $warehouse->where('a.lease_status', $request->status);
        }
        /*
         * 报错优化如下代码：
         *
//        //查看登陆用户下所有申请租赁仓库id
//        $own_application = LeaseApplicationInfo::with('warehouse')->where('owner_id',app('auth')->id());
//        if ($request->filled('status') && $request->status> 0 ) {
//            $own_application->where('lease_status', $request->status);
//        }
//        $application = $own_application->paginate($request->input('page_size'));
//        return $application->toArray();
//        //查询对应状态信息
         * */
        $warehouse = $warehouse->paginate($request->input('page_size'));
        return formatRet(0, '查询成功', $warehouse->toArray());
    }

    //租赁方查看申请列表详情 ok
    public function rentersApplyShow($id)
    {
        $owner_id = app('auth')->guard()->user()->getAuthIdentifier();
        $warehouse = Warehouse::getIns()->from('warehouse as w')->join('lease_application_info as a', 'w.id', '=',
            'a.warehouse_id')
            ->where('a.owner_id', '=', $owner_id)
            ->where('a.id', '=', $id)
            ->first();

        if (!$warehouse) {
            return formatRet(1, '查询成功,用户和数据不匹配');
        }
        return formatRet(0, '查询成功', $warehouse->toArray());
    }

    //仓库产权方 查看申请列表 ok
    public function ownerApplyList(Request $request)
    {
        $this->validate($request, [
            'page' => 'integer|min:1',
            'page_size' => new PageSize,
            'warehouse_id' => 'string|max:255',
            'status' => 'integer',
        ]);

        $own_warehouses = Warehouse::whose(app('auth')->id())
            ->where('type', Warehouse::TYPE_OPEN)
            ->get(['id']);

        $warehouse_ids = $own_warehouses
            ? array_column($own_warehouses->toArray(), 'id')
            : [];

        $leaseApplicationInfo = LeaseApplicationInfo::latest();

        if ($request->filled('warehouse_id')) {
            if (!in_array($request->warehouse_id, $warehouse_ids)) {
                return formatRet(500, '无权限访问该仓库数据');
            }

            $leaseApplicationInfo->ofWarehouse($request->warehouse_id);
        } else {
            $leaseApplicationInfo->whereIn('warehouse_id', $warehouse_ids);
        }

        if ($request->filled('status') && $request->status > 0) {
            $leaseApplicationInfo->where('lease_status', $request->status);
        }

        $lists = $leaseApplicationInfo->paginate($request->input('page_size'));

        return formatRet(0, '查询成功', $lists->toArray());
    }

    //仓库产权方 申请列表详情 ok
    public function ownerApplyShow($lease_id)
    {
        $applicationInfo = LeaseApplicationInfo::with('user')->where('id', $lease_id)->first();
        if ($applicationInfo) {
            return formatRet(0, '', $applicationInfo->toArray());
        }
        return formatRet(1, '');
    }

    //仓库产权房 审核  审核通过需要修改is_used属性
    public function ownerCheck(Request $request)
    {
        $this->validate($request, [
            'lease_id' => 'required|string|max:255',
            'status' => 'required|integer|min:2',
            'remark' => 'string|max:255'
        ]);

        if (!$info = LeaseApplicationInfo::find($request->lease_id)) {
            return formatRet(1, '没有此条记录');
        }
        //租赁限制数量

        if (!$info->applicant->extra || $info->applicant->extra->is_certificated_renter == 0) {
            return formatRet(403, '请先通过租赁仓库的认证');
        }

        if ($info->applicant->extra->isRentMax()) {
            return formatRet(500, '拥有的租赁仓库数已达上限');
        }

        if ($info->lease_status > 1) {
            return formatRet(1, '已经审核完毕');
        }
        //test
        $owner_id = app('auth')->guard()->user()->getAuthIdentifier();
        $info->lease_status = $request->status;
        $info->check_user_id = $owner_id;
        $info->check_data = time();
        $info->remark = $request->input('remark');//owner_id
        app("db")->beginTransaction();
        if (!$info->save()) {
            app("db")->rollback();
            return formatRet(422, '审核失败');
        }
        if ($request->status == 2) {
            if (!Warehouse::addEmployee(WarehouseEmployee::ROLE_RENTER,$info->owner_id,$info['warehouse_id'])) {
                app("db")->rollback();
                return formatRet(422, '租赁权限添加失败');
            }
        }
        app("db")->commit();
        return formatRet(0, '');
    }
}