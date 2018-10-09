<?php

namespace App\Http\Controllers\Open;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Rules\PageSize;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\ProductSpec;
use App\Models\Pick;
use App\Jobs\NotifyThirdParty;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        app('log')->info('API 新增出库单', $request->post());

        $this->validate($request, [
            'out_sn'                      => 'required|string',// 外部订单号
            // 出库单
            'delivery_date'               => 'required|date_format:"Y-m-d"',
            'express_code'                => 'required|in:nle,express,agency,postnl,eax',
            'express_num'                 => 'required_if:express_code,eax',
            'is_tobacco'                  => 'required|boolean',
            'remark'                      => 'string|max:255',
            // 发件人信息
            'send_country'                => 'required|string',
            'send_city'                   => 'required|string',
            'send_postcode'               => 'required|string',
            'send_doorno'                 => 'required|string',
            'send_address'                => 'required|string',
            'send_fullname'               => 'required|string',
            'send_phone'                  => 'required|string',
            // 收件人信息
            'receiver_fullname'           => 'required|string',
            'receiver_phone'              => 'required|string',
            'receiver_country'            => 'required|string|in:NL,CN,BE,DE',
            'receiver_province'           => 'required_if:country,CN|string',
            'receiver_city'               => 'required|string',
            'receiver_postcode'           => 'required|string',
            'receiver_address'            => 'required|string',
            'receiver_doorno'             => 'required_if:country,NL|string',
            // 货品清单
            'goods_data'                  => 'required|array',
            'goods_data.*.relevance_code' => 'required|string|distinct',
            'goods_data.*.num'            => 'required|integer|min:1',
            'goods_data.*.name_cn'        => 'required|string|max:255',
            'goods_data.*.name_en'        => 'required|string|max:255',
        ]);

        // 判断出库单是否存在
        if ($exitst = Order::whose(app('auth')->id())->where('out_sn', $request->out_sn)->first()) {
            return formatRet(500, '订单不能重复推送('.$request->out_sn.')');
        }

        $warehouse = app('auth')->warehouse();

        $items = [];
        $picks = [];
        foreach ($request->goods_data as $v) {
            $spec = ProductSpec::with(['product.category.feature'])
                ->ofWarehouse($warehouse->id)
                ->whose(app('auth')->id())
                ->where('relevance_code', $v['relevance_code'])
                ->first();

            if (! $spec) {
                return formatRet(500, '外部编码 '.$v['relevance_code'].' 不存在');
            }

            // if (! isset($spec->product)) {
            //     return formatRet(500, '外部编码为 '.$v['relevance_code'].' 的规格没有绑定商品');
            // }

            // if (! isset($spec->product->category)) {
            //     return formatRet(500, '商品 '.$spec->product->name_cn.' 没有指定分类');
            // }

            // if (! isset($spec->product->category->feature)) {
            //     return formatRet(500, '分类 '.$spec->product->category->name_cn.' 的特性是无效的');
            // }

            $items[] = [
                'owner_id' => app('auth')->id(),
                'warehouse_id' => $warehouse->id,
                'warehouse_feature_id' => 
                    isset($spec->product->category->feature)
                        ? $spec->product->category->feature->id
                        : 0,
                'relevance_code' => $v['relevance_code'],
                'amount' => $v['num'],
                'name_cn' => $v['name_cn'],
                'name_en' => $v['name_en'],
            ];
        }

        $order = new Order;
        $order->source         = 'api';
        $order->user_app_id    = app('auth')->userApp()->id;
        $order->out_sn         = $request->out_sn;
        $order->owner_id       = app('auth')->id();
        $order->warehouse_id   = $warehouse->id;
        $order->status         = Order::STATUS_DEFAULT;
        $order->order_type     = 1;
        $order->delivery_date  = strtotime($request->delivery_date);// 派送时间
        $order->is_tobacco     = $request->is_tobacco;// 是否烟酒
        $order->express_code   = $request->express_code;
        $order->remark         = $request->input('remark', '');
        // 订单收件人信息
        $order->receiver_country  = $request->receiver_country;
        $order->receiver_province = $request->input('receiver_province');
        $order->receiver_city     = $request->receiver_city;
        $order->receiver_postcode = $request->receiver_postcode;
        $order->receiver_doorno   = $request->input('receiver_doorno');
        $order->receiver_address  = $request->receiver_address;
        $order->receiver_fullname = $request->receiver_fullname;
        $order->receiver_phone    = $request->receiver_phone;
        // 发件人信息，即仓库地址
        $order->send_country  = $request->send_country;
        $order->send_city     = $request->send_city;
        $order->send_postcode = $request->send_postcode;
        $order->send_doorno   = $request->send_doorno;
        $order->send_address  = $request->send_address;
        $order->send_fullname = $request->send_fullname;
        $order->send_phone    = $request->send_phone;
	    $order->mask_code     = $order->newMaskCode();

        app('db')->beginTransaction();
        try {
            $order->save();

            $order->express_num = $express_num = Order::makeExpressNum($warehouse->code, $order->id, $order->express_code);
            $order->save();

            OrderHistory::addHistory($order, Order::STATUS_DEFAULT);

            $shipment_nums = [];

            // 一个出库单某个特性的拣货有且只有一个
            foreach ($items as $k => $item) {

                $warehouse_feature_id = $item['warehouse_feature_id'];

                // 当前特性与没有生成拣货单号
                if (isset($shipment_nums[$warehouse_feature_id])) {
                    // 有则直接用
                    $shipment_num = $shipment_nums[$warehouse_feature_id];
                } else {
                    // 没有生成一个
                    $shipment_num = Pick::makeShipmentNum($warehouse->code, intval($order->id.$warehouse_feature_id));
                    $shipment_nums[$warehouse_feature_id] = $shipment_num;

                    $picks[] = [
                        'warehouse_id' => $warehouse->id,
                        'warehouse_feature_id' => $item['warehouse_feature_id'],
                        'shipment_num' => $shipment_num,
                        'status' => Pick::STATUS_DEFAULT,
                        'verify_status' => Pick::VERIFY_STATUS_INIT,
                    ];
                }

                $items[$k]['shipment_num'] = $shipment_num;
            }

            $order->orderItems()->createMany($items);

            $order->picks()->createMany($picks);

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500, '出库单新增失败');
        }

        $picksData = [];
        foreach ($order->picks as $pick) {
            $picksData[] = [
                'shipment_num' => $pick->shipment_num,
                'feature_logo' => isset($pick->feature->logo) ? $pick->feature->logo : '',
            ];
        }

        return formatRet(0, '', [
            'express_num' => $express_num,
            'mask_code' => $order->mask_code,
            'shipment_nums' => $picksData,
        ]);
    }

    /**
     * 预约派送
     */
    public function appointment(Request $request)
    {
        app('log')->info('API 预约', $request->post());

        $this->validate($request, [
            'out_sn'            => 'required|string',// 外部订单号
            'delivery_date'     => 'required|date_format:"Y-m-d"',
            'line_name'         => 'present|string',
            'receiver_fullname' => 'required|string',
            'receiver_phone'    => 'required|string',
            'receiver_country'  => 'required|string|in:NL,CN,BE,DE',
            'receiver_province' => 'required_if:country,CN|string',
            'receiver_city'     => 'required|string',
            'receiver_postcode' => 'required|string',
            'receiver_address'  => 'required|string',
            'receiver_doorno'   => 'required|string',
        ]);

        $warehouse = app('auth')->warehouse();

        $order = Order::whose(app('auth')->id())
            ->ofWarehouse($warehouse->id)->where('out_sn', $request->out_sn)->first();

        if (! $order) {
            return formatRet(500, '订单不存在');
        }

        if (! in_array($order->express_code, ['nle', 'agency', 'eax', 'express'])) {
            return formatRet(500, '订单快递公司必须要是 nle、express、agency 或者 eax');
        }

        app('db')->beginTransaction();
        try {
            // 订单收件人信息
            $order->delivery_date     = $request->delivery_date;
            $order->line_name         = $request->line_name;
            $order->receiver_country  = $request->receiver_country;
            $order->receiver_province = $request->input('receiver_province');
            $order->receiver_city     = $request->receiver_city;
            $order->receiver_postcode = $request->receiver_postcode;
            $order->receiver_doorno   = $request->receiver_doorno;
            $order->receiver_address  = $request->receiver_address;
            $order->receiver_fullname = $request->receiver_fullname;
            $order->receiver_phone    = $request->receiver_phone;
            $order->save();

            // if ($order['is_plan_erp'] == Order::ORDER_PLAN_CANCEL){
            //         Order::where('id', $order->id)->where('status', Order::STATUS_SENDING)
            //             ->update(['status' => Order::STATUS_WAITING]);
            //         Pick::where('order_id', $order->id)->where('status', Pick::STATUS_SENDING)
            //             ->update(['status' => Pick::STATUS_WAITING]);
            // }

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500, '数据库发生了错误');
        }

        if ($history = $order->historys()->where('status', Order::STATUS_WAITING)->first()) {
            dispatch(new NotifyThirdParty($order, $history));
        }

        return formatRet(0, '预约成功');
    }

    /**
     * 取消预约
     */
    public function cancel(Request $request)
    {
        app('log')->info('API 取消出库单', $request->post());

        $this->validate($request, [
            'out_sn' => 'required|string',// 外部订单号
        ]);

        $user = app('auth')->user();
        $warehouse = app('auth')->warehouse();

        $order = Order::whose($user->id)
            ->ofWarehouse($warehouse->id)
            ->where('out_sn', $request->out_sn)
            ->first();

        if (! $order) {
            return formatRet(500, '订单不存在');
        }

        switch ($order->status) {
            case Order::STATUS_CANCEL:
                return formatRet(500, '已退单，不能取消预约');
                break;
            case Order::STATUS_DEFAULT:// 待拣货
            case Order::STATUS_PICKING:// 拣货中
            case Order::STATUS_PICK_DONE:// 已拣货
            case Order::STATUS_WAITING:// 待出库（已验货）
                break;
            case Order::STATUS_SENDING:// 配送中
                return formatRet(500, '配送中，不能取消预约');
            case Order::STATUS_SUCCESS:// 已收货
                return formatRet(500, '已签收，不能取消预约');
            default:
                return formatRet(500, '订单状态异常');
                break;
        }

        // app('db')->beginTransaction();
        try {
            Order::where('id', $order->id)
                // ->where('status', '!=', Order::STATUS_SENDING)
                // ->where('status', '!=', Order::STATUS_SUCCESS)
                // ->where('is_plan_erp', Order::ORDER_PLAN_HAS)
                ->update([
                    'old_plan_status'   =>  $order->status,
                    'delivery_date'     =>  NULL,
                    'line_name'         =>  '',
                    'receiver_country'  =>  '',
                    'receiver_city'     =>  '',
                    'receiver_postcode' =>  '',
                    'receiver_doorno'   =>  '',
                    'receiver_address'  =>  '',
                    'receiver_fullname' =>  '',
                    'receiver_phone'    =>  '',
                    'receiver_email'    =>  '',
                ]);
            // app('db')->commit();
        } catch (\Exception $e) {
            // app('db')->rollback();
            return formatRet(500, '操作失败');
        }

        return formatRet(0, '取消预约成功');
    }

    /**
     * 派送失败
     */
    public function failed_delivery(Request $request)
    {
        app('log')->info('API 派送失败', $request->post());

        $this->validate($request, [
            'out_sn' => 'required|string',// 外部订单号
        ]);

        $user = app('auth')->user();
        $warehouse = app('auth')->warehouse();

        if (! $order = Order::whose($user->id)->ofWarehouse($warehouse->id)->where('out_sn', $request->out_sn)->first()) {
            return formatRet(500, '订单不存在');
        }

        if ($order->status != Order::STATUS_SENDING) {
            return formatRet(500, '状态不是配送中');
        }

        app('db')->beginTransaction();
        try {
            Order::where('id', $order->id)->where('status', Order::STATUS_SENDING)->update([
                'status'            => Order::STATUS_WAITING,
                'delivery_date'     => NULL,
                'line_name'         => '',
                'receiver_country'  => '',
                'receiver_city'     => '',
                'receiver_postcode' => '',
                'receiver_doorno'   => '',
                'receiver_address'  => '',
                'receiver_fullname' => '',
                'receiver_phone'    => '',
                'receiver_email'    => '',
            ]);

            Pick::where('order_id', $order->id)->where('status', Pick::STATUS_SENDING)->update([
                'status' => Pick::STATUS_WAITING,
            ]);

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500, '操作失败');
        }

        return formatRet(0, '成功，订单状态已更新为派送失败');
    }

    /**
     * 退款
     */
    public function refund(Request $request)
    {
        app('log')->info('API 退款', $request->post());

        $this->validate($request, [
            'out_sn' => 'required|string',// 外部订单号
        ]);

        $warehouse = app('auth')->warehouse();

        $order = Order::whose(app('auth')->id())
            ->ofWarehouse($warehouse->id)->where('out_sn', $request->out_sn)->first();

        if (! $order) {
            return formatRet(500, '订单不存在');
        }

        if ($order->status == Order::STATUS_CANCEL) {
            return formatRet(500, '订单已经退款');
        }

        if ($order->status != Order::STATUS_DEFAULT) {
            return formatRet(500, '不能退款');
        }

        app('db')->beginTransaction();
        try {
            Order::where('id', $order->id)
                ->where('status', Order::STATUS_DEFAULT)
                ->update(['status' => Order::STATUS_CANCEL]);

            Pick::where('order_id', $order->id)
                ->where('status', Pick::STATUS_DEFAULT)
                ->update(['status' => Pick::STATUS_CANCEL]);

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500, '操作失败');
        }

        return formatRet(0, '退款成功');
    }

    /**
     * 司机发货
     */
    public function delivery(Request $request)
    {
        app('log')->info('API 发货', $request->post());

        $this->validate($request, [
            'express_num' => 'required|string',
        ]);

        $warehouse = app('auth')->warehouse();

        $order = Order::whose(app('auth')->id())->ofWarehouse($warehouse->id)->where('express_num', $request->express_num)->first();

        if (! $order) {
            return formatRet(500, '订单不存在');
        }

        if ($order->status != Order::STATUS_WAITING) {
            return formatRet(500, '状态不是待出库');
        }

        app('db')->beginTransaction();
        try {
            // 更新出库单
            Order::where('id', $order->id)->where('status', Order::STATUS_WAITING)->update(['status' => Order::STATUS_SENDING]);
            // 更新拣货单
            Pick::where('order_id', $order->id)->where('status', Pick::STATUS_WAITING)->update(['status' => Pick::STATUS_SENDING]);

            OrderHistory::addHistory($order, Order::STATUS_SENDING);

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500, '操作失败');
        }

        return formatRet(0, '发货成功');
    }

    /**
     * 客户签收
     */
    public function receipt(Request $request)
    {
        app('log')->info('API 签收', $request->post());

        $this->validate($request, [
            'express_num'   =>  'required|string',
        ]);

        $warehouse = app('auth')->warehouse();

        $order = Order::whose(app('auth')->id())->ofWarehouse($warehouse->id)->where('express_num', $request->express_num)->first();

        if (! $order) {
            return formatRet(500, '订单不存在');
        }

        if ($order->status != Order::STATUS_SENDING) {
            return formatRet(500, '状态不是配送中');
        }

        app('db')->beginTransaction();
        try {
            // 更新出库单
            Order::where('id', $order->id)->where('status', Order::STATUS_SENDING)->update(['status' => Order::STATUS_SUCCESS]);
            // 更新拣货单
            Pick::where('order_id', $order->id)->where('status', Pick::STATUS_SENDING)->update(['status' => Pick::STATUS_SUCCESS]);

            OrderHistory::addHistory($order, Order::STATUS_SUCCESS);

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500, '操作失败');
        }

        return formatRet(0, '签收成功');
    }
}
