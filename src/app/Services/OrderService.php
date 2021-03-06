<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace  App\Services;

use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\ProductSpec;
use App\Models\ReceiverAddress;
use App\Models\SenderAddress;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;
use App\Events\OrderCreated;
use App\Events\OrderShipped;
use App\Events\OrderPaid;
use App\Events\OrderCompleted;

class OrderService
{

    private $source = '';

    public function setSource($v)
    {
        $this->source = $v;
        return $this;
    }

    public function getSource()
    {
        return $this->source;
    }

    /**
     * 更新快递公司
     **/
    public function updateExpress($data, $id, $onlyUpdateDb = false)
    {
        $order = Order::find($id);
        if(!$order) {
            throw new \Exception("订单未找到", 1);
        }

        if(!app('ship')->validCode($data->express_code)){
            throw new \Exception("快递公司不合法", 1);
        }

        $arr = [
                'express_code'  =>  $data->express_code,
                'express_num'   =>  $data->express_num,
                'shop_remark'   =>  $data->shop_remark??'',
                'status'        =>  Order::STATUS_SENDING //配送中
            ];

        if($onlyUpdateDb) {
            unset($arr['status']);
        }
        $order->update($arr);

        if(!$onlyUpdateDb) {
            event(new OrderShipped($order));
            OrderHistory::addHistory($order, Order::STATUS_SENDING);
        }

        return true;

    }

    /**
     * 更新为发货
     **/
    public function updateSend($id)
    {
        $order = Order::find($id);
        if(!$order) {
            throw new \Exception("订单未找到", 1);
        }


        $arr = [
                'status'        =>  Order::STATUS_SENDING //配送中
            ];


        $order->update($arr);

        event(new OrderShipped($order));
        OrderHistory::addHistory($order, Order::STATUS_SENDING);

        return true;

    }

    /**
     * 设置为公开信息
     **/
    public function updateToShare($id)
    {
        $order = Order::find($id);
        if(!$order) {
            throw new \Exception("订单未找到", 1);
        }
        if($order->share_code !="") return $order->share_code;

        $arr = [
            'share_code'    =>  md5($id.time().$order->out_sn)
            ];

        $order->update($arr);

        return $arr['share_code'];

    }



    /**
     * 更新支付信息
     **/
    public function updatePay($data, $id)
    {

        $order = Order::find($id);
        if(!$order) {
            throw new \Exception("订单未找到", 1);
        }

        if(!in_array($data->pay_status, [Order::ORDER_PAY_STAUTS_PAID, Order::ORDER_PAY_STATUS_UNPAY, Order::ORDER_PAY_STATUS_REFUND])){
            throw new \Exception("支付状态非法:".$data->pay_status, 1);
        }


        if(!in_array($data->pay_type, [0, Order::ORDER_PAY_TYPE_ALIPAY, Order::ORDER_PAY_TYPE_WECHAT, Order::ORDER_PAY_TYPE_BANK, Order::ORDER_PAY_TYPE_CASH, Order::ORDER_PAY_TYPE_OTHER])){
            throw new \Exception("支付方式非法", 1);
        }

        if(intval($data->pay_status) >0 && intval($data->pay_type) == 0){
            throw new \Exception("请选择支付方式", 1);
        }

        $order->update(
            [
                'pay_type'                  =>  $data->pay_type,
                'pay_status'                =>  $data->pay_status,
                'sub_pay'                   =>  $data->sub_pay,
                'payment_account_number'    =>  $data->payment_account_number??'',
            ]
        );


        if(intval($data["pay_status"]) == Order::STATUS_PAID) {
            event(new OrderPaid($order));
            OrderHistory::addHistory($order, Order::STATUS_PAID);
        }


        return true;
    }

    /**
     * 更新状态为签收
     **/
    public function updateRceived($data, $id)
    {
        $order = Order::find($id);
        if(!$order) {
            throw new \Exception("订单未找到", 1);
        }


        $order->update(
            [
                'status'    =>  Order::STATUS_SUCCESS,
            ]
        );

        event(new OrderCompleted($order));
        OrderHistory::addHistory($order, Order::STATUS_SUCCESS);
    }

    /**
     * 创建订单
     **/
    public  function create($data, $userId = 0)
    {

        $user_id = ($userId == 0) ?Auth::ownerId():$userId;

        try {
            $lock = Cache::lock(sprintf("orderCreateUserLockV1:%s", $user_id), 5);
            //加一个锁防止并发
            if ($lock->get()) {

                //如果是外部传进来的
                if(isset($data->out_sn) && !empty($data->out_sn)) {

                    $out_sn = $data->out_sn;
                    $exists = Order::query()
                        ->where('out_sn', $out_sn)
                        ->where('warehouse_id', $data->warehouse_id)
                        ->lockForUpdate()
                        ->first();

                    if($exists) {
                        throw new \Exception("{$out_sn} 已经存在", 1);

                    }
                } else {
                    $out_sn = Order::generateOutSn();
                }


                $subTotal = 0;
                $subQty = 0;
                $items=[];
                foreach ($data->goods_data as $k => $v) {

                    $spec = ProductSpec::with(['product.category.feature'])
                        ->ofWarehouse($data->warehouse_id)
                        ->whose($user_id)
                        ->where('relevance_code', $v['relevance_code'])
                        ->first();
                    if(!$spec) throw new \Exception("{$v['relevance_code']} 不存在", 1);

                    //检查库存数
                    if ($spec['total_stock_num'] < $v['num']) {
                        throw new BusinessException("商品{$spec->product->name_cn}出库数量大于库存数");
                    }

                    $items[] = [
                        'owner_id' => $user_id,
                        'warehouse_id' => $data->warehouse_id,
                        'warehouse_feature_id' =>
                            isset($spec->product->category->feature)
                                ? $spec->product->category->feature->id
                                : 0,
                        'relevance_code' => $v['relevance_code'],
                        'amount' => $v['num'],
                        'name_cn' => $spec->product->name_cn??'',
                        'sale_price' => $v['sale_price']??0,
                        'sale_currency'=> $data->sale_currency??'CNY',
                        'purchase_price' => $spec->purchase_price??0,
                        'purchase_currency' => $spec->purchase_currency??"CNY",
                        'name_en' => $spec->product->name_en??'',
                        'spec_name_cn' => $spec->name_cn,
                        'spec_name_en' => $spec->name_en,
                        'pic' => $v['pic']??$spec->product->photos,
                        'spec_id'=>$spec->id,
                    ];

                    $subQty += $v['num'];
                    $subTotal += ($v['sale_price']??0) * $v['num'];
                }

                $order = new Order();
                $order->owner_id       = $user_id;
                $order->order_type     = $data->order_type;

                if($data->filled('delivery_date')){
                    $order->delivery_date  = strtotime($data->delivery_date);
                }

                if($data->filled('delivery_type')){
                    $order->delivery_type  = $data->input('delivery_type');
                }

                $order->warehouse_id   = $data->warehouse_id;
                $order->status         = Order::STATUS_DEFAULT;
                $order->remark         = $data->remark??'';
                $order->sale_currency  = $data->sale_currency??'CNY';
                $order->source         = $this->getSource();
                $order->sub_order_qty  = $subQty;
                // 收件人信息
                if (! isset($data->receiver)) {
                    $receiver = ReceiverAddress::find($data->receiver_id);
                } else {
                    $receiver = $data->receiver;
                }

                $order->receiver_country  = $receiver->country;
                $order->receiver_province = $receiver->province;
                $order->receiver_city     = $receiver->city;
                $order->receiver_postcode = $receiver->postcode;
                $order->receiver_district   = $receiver->district;
                $order->receiver_address  = $receiver->address;
                $order->receiver_fullname = $receiver->fullname;
                $order->receiver_phone    = $receiver->phone;

                // 发件人信息
                if(!isset($data->sender))
                {
                    if($data->sender_id == 0 ) {
                        $warehouse = Warehouse::query()->findOrFail($data->warehouse_id);

                        $sender = new SenderAddress;
                        $sender->country    = $warehouse['country'];
                        $sender->city       = $warehouse['city'];
                        $sender->province   = $warehouse['province'];
                        $sender->postcode   = $warehouse['postcode'];
                        $sender->district   = $warehouse['street'];
                        $sender->address    = $warehouse['door_no'];
                        $sender->fullname   = $warehouse['name_cn'];
                        $sender->phone      = $warehouse['contact_number'] ?? '';
                    } else {
                        $sender = SenderAddress::where("owner_id", Auth::ownerId())->find($data->sender_id);
                        if(!$sender) {
                            throw new \Exception("发件人信息不存在", 1);

                        }
                    }

                }
                else
                {
                    $sender = $data->sender;
                }
                $order->send_country  = $sender->country;
                $order->send_city     = $sender->city;
                $order->send_province = $sender->province;
                $order->send_postcode = $sender->postcode;
                $order->send_district = $sender->district;
                $order->send_address  = $sender->address;
                $order->send_fullname = $sender->fullname;
                $order->send_phone    = $sender->phone;
                $order->out_sn        = $out_sn;


                $order->express_num     = $data->express_num;
                $order->shop_id         = $data->shop_id??0;
                $order->shop_user_id    = $data->shop_user_id??0;

                $order->sub_total    = $subTotal;

                $order->save();
                OrderHistory::addHistory($order, Order::STATUS_DEFAULT);
                $order->orderItems()->createMany($items);

                $lock->release();

                event(new OrderCreated($order));
                return $order;
            } else {
                throw new \Exception("锁不能释放", 1);
            }
        } catch (BusinessException $ex) {
            $lock->release();
            throw $ex;
        } catch (\Exception $ex) {
            $lock->release();
            throw new \Exception($ex->getMessage(), 1);
        }
    }

    /**
     * 更新订单数量
     */
    public function UpdateData($data,$order)
    {
        $order->orderItems()->delete();
        $order->historys()->delete();

        $user_id = Auth::ownerId();
        $items=[];
        foreach ($data->goods_data as $k => $v) {

            $spec = ProductSpec::with(['product.category.feature'])
                ->ofWarehouse($data->warehouse_id)
                ->whose($user_id)
                ->where('relevance_code', $v['relevance_code'])
                ->first();
            $items[] = [
                'owner_id' => $user_id,
                'warehouse_id' => $data->warehouse_id,
                'warehouse_feature_id' =>
                    isset($spec->product->category->feature)
                        ? $spec->product->category->feature->id
                        : 0,
                'relevance_code' => $v['relevance_code'],
                'amount' => $v['num'],
                'name_cn' => $spec->product_name_cn,
                'name_en' => $spec->product_name_en,
            ];
        }

        $order->order_type     = $data->order_type;
        $order->delivery_date  = $data->delivery_date;
        $order->delivery_type  = $data->delivery_type;
        $order->warehouse_id   = $data->warehouse_id;
        $order->status         = Order::STATUS_DEFAULT;

        // 收件人信息
        $receiver = ReceiverAddress::find($data->receiver_id);
        $order->receiver_country  = $receiver->country;
        $order->receiver_province = $receiver->province;
        $order->receiver_city     = $receiver->city;
        $order->receiver_postcode = $receiver->postcode;
        $order->receiver_district   = $receiver->district;
        $order->receiver_address  = $receiver->address;
        $order->receiver_fullname = $receiver->fullname;
        $order->receiver_phone    = $receiver->phone;

        // 发件人信息
        $sender = SenderAddress::find($data->sender_id);
        $order->send_country  = $sender->country;
        $order->send_city     = $sender->city;
        $order->send_province = $sender->province;
        $order->send_postcode = $sender->postcode;
        $order->send_district   = $sender->district;
        $order->send_address  = $sender->address;
        $order->send_fullname = $sender->fullname;
        $order->send_phone    = $sender->phone;

        $order->express_num = $data->express_num;
        $order->save();
        OrderHistory::addHistory($order, Order::STATUS_DEFAULT);
        $order->orderItems()->createMany($items);
    }
}
