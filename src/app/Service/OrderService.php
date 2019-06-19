<?php
namespace  App\Services\Service;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\ProductSpec;
use App\Models\ReceiverAddress;
use App\Models\SenderAddress;
use Illuminate\Support\Facades\Auth;

class OrderService
{

    public  function create($data)
    {
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

        $order = new Order();
        $order->owner_id       = $user_id;
        $order->order_type     = $data->order_type;
        if($data->filled('delivery_date')){
            $order->delivery_date  = $data->delivery_date;
        };
        if($data->filled('delivery_type')){
            $order->delivery_type  = $data->input('delivery_type');
        }
        $order->warehouse_id   = $data->warehouse_id;
        $order->status         = Order::STATUS_DEFAULT;
        $order->remark         = $data->input("remark","");

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

        $order->out_sn = Order::generateOutSn();
        $order->express_num = $data->express_num;
        $order->save();
        OrderHistory::addHistory($order, Order::STATUS_DEFAULT);
        $order->orderItems()->createMany($items);

    }


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