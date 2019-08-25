<?php
namespace  App\Services\Service;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\ProductSpec;
use App\Models\ReceiverAddress;
use App\Models\SenderAddress;
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
            $lock = Cache::lock(sprintf("orderCreateUserLock:%s", $user_id));
            //加一个锁防止并发
            if ($lock->get()) {

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
                        'sale_currency'=> $data->input("sale_currency","CNY"),
                        'purchase_price' => $spec->purchase_price??0,
                        'purchase_currency' => $spec->purchase_currency??"CNY",
                        'name_en' => $spec->product->name_en??'',
                        'spec_name_cn' => $spec->name_cn,
                        'spec_name_en' => $spec->name_en,
                        'pic' => $v['pic']??'',
                    ];

                    $subQty += $v['num'];
                    $subTotal += ($v['sale_price']??0);
                }

                $order = new Order();
                $order->owner_id       = $user_id;
                $order->order_type     = $data->order_type;
                if($data->filled('delivery_date')){
                    $order->delivery_date  = strtotime($data->delivery_date);
                };
                if($data->filled('delivery_type')){
                    $order->delivery_type  = $data->input('delivery_type');
                }
                $order->warehouse_id   = $data->warehouse_id;
                $order->status         = Order::STATUS_DEFAULT;
                $order->remark         = $data->input("remark","");
                $order->sale_currency  = $data->input("sale_currency","CNY"); 
                $order->source         = $this->getSource();
                $order->sub_order_qty  = $subQty;
                // 收件人信息
                if(!isset($data->receiver))
                {
                    $receiver = ReceiverAddress::find($data->receiver_id);
                } 
                else 
                {
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
                    $sender = SenderAddress::find($data->sender_id);
                } 
                else 
                {
                    $sender = $data->sender;
                }
                $order->send_country  = $sender->country;
                $order->send_city     = $sender->city;
                $order->send_province = $sender->province;
                $order->send_postcode = $sender->postcode;
                $order->send_district   = $sender->district;
                $order->send_address  = $sender->address;
                $order->send_fullname = $sender->fullname;
                $order->send_phone    = $sender->phone;


                $order->out_sn = Order::generateOutSn();
                $order->express_num = $data->express_num;
                $order->shop_id    = $data->shop_id??0;
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
        } 
        catch(\Exception $ex) {
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