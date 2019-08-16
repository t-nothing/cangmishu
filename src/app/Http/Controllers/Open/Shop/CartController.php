<?php
/**
 * 店铺购物车
 */

namespace App\Http\Controllers\Open\Shop;
use App\Http\Requests\BaseRequests;
use App\Http\Controllers\Controller;
use App\Rules\PageSize;
// use Overtrue\LaravelShoppingCart\Cart;
use Illuminate\Support\Facades\Auth;
use App\Models\ShopProductSpec;
use App\Models\ReceiverAddress;
use App\Models\SenderAddress;
use App\Http\Requests\CreateShopCartCheckoutRequest;
use Cart;

class CartController extends Controller
{
    // var $cart;

    public function getInstanceName()
    {
        return 'shopping';
    }

    public function getWhoesCart()
    {
        $key = sprintf("%s:shop-%d:cart", Auth::user()->id, 1);
        return $key;
    }

    public function load($shopId)
    {
        Cart::instance($this->getInstanceName())->restore($this->getWhoesCart());
    }

    /**
     * 添加购物车
     **/
    public function store(BaseRequests $request)
    {
        $this->validate($request, [
            'spec_id'           => 'required|integer|min:1',
            'qty'               => 'required|integer|min:1',
        ]);


        $spec = ShopProductSpec::with('product')->where('shop_id', $request->shop->id)->find($request->spec_id);
        if(!$spec)
        {
            return formatRet(500,"添加购物车失败,商品不存在");
        }


        try
        {
            $this->load($request->shop->id);
            $spec->load('productSpec');
            Cart::add($spec->id, $spec->product->name, $request->qty, $spec->sale_price, 1, [
                'spec'              =>  $spec->name,
                'source'            =>  'wechat.mini_program',
                'relevance_code'    =>  $spec->productSpec->relevance_code
            ]);
            Cart::store($this->getWhoesCart());

            return formatRet(200,"添加购物车成功");
        }
        catch(\Exception $ex)
        {

            print_r($ex->getMessage());
        }

        
        return formatRet(500,"添加购物车失败");
        
    }

    /**
     * 更新数量
     **/
    public function updateQty(BaseRequests $request, $id, $qty)
    {
        
        try
        {
            $this->load($request->shop->id);
            Cart::update($id, $qty);

            return formatRet(200,"更新商品成功");
        }
        catch(\Exception $ex)
        {

        }

        
        return formatRet(500,"更新商品失败");
    }

    /**
     * 移除单个商品
     **/
    public function remove(BaseRequests $request, $id)
    {
        
        try
        {
            $this->load($request->shop->id);
            Cart::remove($id);

            return formatRet(200,"移除商品成功");
        }
        catch(\Exception $ex)
        {

        }

        
        return formatRet(500,"移除商品失败");
    }

    /**
     * 清空购物车
     **/
    public function destroy(BaseRequests $request)
    {
        try
        {
            $this->load($request->shop->id);
            Cart::destroy();

            return formatRet(200,"清空购物车成功");
        }
        catch(\Exception $ex)
        {

        }

        
        return formatRet(500,"清空购物车失败");
    }


    /**
     * 购物车列表
     **/
    public function list(BaseRequests $request)
    {
        $this->load($request->shop->id);

        return formatRet(0, '', Cart::content()->toArray());
    }

    /**
     * 统计数量
     **/
    public function count(BaseRequests $request)
    {
        $this->load($request->shop->id);
        return formatRet(0, '', [
            'count'         =>  Cart::count(),
            'total'         =>  Cart::total(),
            'discount'      =>  Cart::discount()
        ]);
    }

    /**
     * 结算提交订单
     **/
    public function checkout(CreateShopCartCheckoutRequest $request)
    {
        app('log')->info('店铺下单',$request->all());
        app('db')->beginTransaction();
        try {

            $this->load($request->shop->id);
            if($request->verify_money != Cart::total())
            {
                throw new \Exception("下单金额不一致", 1);
            }

            if(0 === Cart::count())
            {
                throw new \Exception("购物车不能为空", 1);
            }

            $data = new BaseRequests;            

            $data->delivery_type  = "";
            $data->remark = "";
            $data->shop_id = $request->shop->id;
            $data->shop_user_id = Auth::user()->id;
            $data->warehouse_id =  $request->shop->warehouse_id;
            $data->order_type = 1;
            $data->delivery_type = "微店铺下单";
            $data->shop_remark = "微店铺下单";
            $data->remark = "用户备注";
            $data->express_num = "";
            $data->source = "mini_program";


            $data->receiver = new ReceiverAddress([
                "country"       =>  $request->country,
                "province"      =>  $request->province,
                "city"          =>  $request->city,
                "postcode"      =>  $request->postcode,
                "district"      =>  $request->district,
                "address"       =>  $request->address,
                "fullname"      =>  $request->fullname,
                "phone"         =>  $request->phone,
            ]);

            //查找店铺默认发件人

            $data->sender = new SenderAddress([
                "country"       =>  $request->shop->senderAddress->country,
                "province"      =>  $request->shop->senderAddress->province,
                "city"          =>  $request->shop->senderAddress->city,
                "postcode"      =>  $request->shop->senderAddress->postcode,
                "district"      =>  $request->shop->senderAddress->district,
                "address"       =>  $request->shop->senderAddress->address,
                "fullname"      =>  $request->shop->senderAddress->fullname,
                "phone"         =>  $request->shop->senderAddress->phone
            ]);



            $orderItem = [];
            foreach(Cart::content() as $row)  {
                $orderItem[] = [
                    'relevance_code'    =>  $row->options['relevance_code'],
                    'num'               =>  $row->qty,
                ];
            }

           
            $data->goods_data = collect($orderItem);
            
            app('order')->create($data, $request->shop->owner_id);
            app('db')->commit();

            Cart::destroy();

        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('下单失败',['msg'=>$e->getMessage()]);
            return formatRet(500, '下单失败:'.$e->getMessage());
        }
        return formatRet(0,'出库单新增成功');
    }
}