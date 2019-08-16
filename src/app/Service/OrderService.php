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

    public  function create($data, $userId = 0)
    {

        $user_id = ($userId == 0) ?Auth::ownerId():$userId;
        
        try {
            $lock = Cache::lock(sprintf("orderCreateUserLock:%s", $user_id));
            //加一个锁防止并发
            if ($lock->get()) {

                $subTotal = 0;
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
                        'name_cn' => $spec->product_name_cn,
                        'sale_price' => $v['sale_price']??0,
                        'name_en' => $spec->product_name_en,
                    ];


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
                $order->source         = $this->getSource();

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

                //这个地方可以加一个服务
                //@todo 下单邮件通知
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