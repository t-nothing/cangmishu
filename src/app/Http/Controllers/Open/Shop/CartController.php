<?php
/**
 * 店铺购物车
 */

namespace App\Http\Controllers\Open\Shop;
use App\Exceptions\BusinessException;
use App\Http\Requests\BaseRequests;
use App\Http\Controllers\Controller;
use App\Models\ProductSpec;
use App\Rules\PageSize;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use App\Models\ShopProductSpec;
use App\Models\ReceiverAddress;
use App\Models\SenderAddress;
use App\Models\OrderType;
use App\Http\Requests\CreateShopCartCheckoutRequest;
use App\Models\ShopWeappFormId;
use App\Events\CartCheckouted;

class CartController extends Controller
{

    public function shopId()
    {
        return app('request')->header('Shop', '');
    }

    public function getInstanceName()
    {
        return 'shopping';
    }

    public function getWhoisCart()
    {
        return sprintf("%s:shop-%d:cart", $this->shopId(), Auth::user()->id);
    }

    /**
     * 接收多个form id
     **/
    private function processFormId(BaseRequests $request){

        app('log')->info('token', [
            'request' => $request->all()
        ]);

        if($request->filled('form_id')) {
            $form_id = $request->input('form_id', NULL);

            if(is_null($form_id)) return;

            $formIdArr = [];
            if(!is_array($form_id)) {
                $formIdArr[] = $form_id;
            } else {
                $formIdArr = $form_id;
            }

            foreach ($formIdArr as $key => $form_id) {
                ShopWeappFormId::create([
                    'form_id'   => $form_id,
                    'user_id'   => Auth::user()->id
                ]);
            }
        }

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

        app('log')->info('添加购物车',  $request->all());

        $spec = ShopProductSpec::with('product')->where('shop_id', $request->shop->id)->find($request->spec_id);
        if(!$spec)
        {
            return formatRet(500,"添加购物车失败,商品不存在");
        }

        // $this->processFormId($request);
        try
        {
            $spec->load('productSpec');

            $cart = new CartService();

            $cartSpec = $cart->name($this->getWhoisCart())->search(['id' => $request['spec_id']]);

            info('查询到已有的购物车规格', $cartSpec->toArray());

            if ($cartSpec->isEmpty()) {
                $cartItem = $cartSpec->first();
                if ($request['qty'] + ($cartItem['qty'] ?? 0) > $spec['productSpec']['total_stock_num']) {
                    throw new BusinessException('库存数量不足');
                }
            }

            $pics = json_decode($spec->product->pics, true);

            $cart->name($this->getWhoisCart())
                ->add($spec->id, $spec->product->name, $request->qty, $spec->sale_price, [
                    'product_id' => $spec->shop_product_id,
                    'spec'              => $spec->name,
                    'source'            => 'wechat.mini_program',
                    'relevance_code'    => $spec->productSpec->relevance_code,
                    'pic'               => $pics[0] ?? '',
                    'currency'          => $request->shop->currency
            ]);

            return formatRet(200,"添加购物车成功");
        } catch (BusinessException $exception) {
            throw $exception;
        } catch(\Exception $ex) {

            // print_r($ex->getMessage());
        }

        return formatRet(500,"添加购物车失败");
    }

    /**
     * 更新数量
     *
     * @param  BaseRequests  $request
     * @param $code
     * @param $qty
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateQty(BaseRequests $request, $code, $qty)
    {
        $cart = new CartService();

        try
        {
            $spec = $cart->name($this->getWhoisCart())->get($code);

            $spec = ShopProductSpec::query()
                ->with(['productSpec'])
                ->findOrFail($spec['id']);

            if ($qty > $spec['productSpec']['total_stock_num']) {
                throw new BusinessException('库存数量不足');
            }

            // $this->processFormId($request);
            app('cart')->name($this->getWhoisCart())->update($code, $qty);

            return formatRet(200, "更新商品成功");
        } catch (\Exception $ex) {

        }

        return formatRet(500,"更新商品失败");
    }

    /**
     * 移除单个商品
     *
     * @param  BaseRequests  $request
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(BaseRequests $request, $code)
    {
        try
        {
            app('cart')->name($this->getWhoisCart())->remove($code);

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
            app('cart')->name($this->getWhoisCart())->destroy();

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
        app('log')->info('token', [
            'token' => app('request')->header('Authorization', ''),
            'shop' => app('request')->header('Shop', ''),
        ]);
        $items = app('cart')->name($this->getWhoisCart())->all();

        $specs = ShopProductSpec::query()
            ->with('productSpec')
            ->whereKey(data_get($items, '*.id'))
            ->get();

        foreach ($items as $key => &$value) {
            try {
                $tmp = $value->__raw_id;
                $value['total_stock_num'] = data_get($specs->first(function ($v) use ($value) {
                    return $v['id'] === $value['id'];
                }), 'productSpec.total_stock_num');
            } catch( \ErrorException $ex) {
                app('cart')->name($this->getWhoisCart())->remove($key);
                unset($items[$key]);
            }
        }

        return formatRet(0, '', $items->toArray());
    }

    /**
     * 统计数量
     **/
    public function count(BaseRequests $request)
    {
        return formatRet(0, '', [
            'count'         =>  app('cart')->name($this->getWhoisCart())->count(),
            'total'         =>  app('cart')->name($this->getWhoisCart())->total(),
        ]);
    }

    /**
     * 结算提交订单
     **/
    public function checkout(CreateShopCartCheckoutRequest $request)
    {
        app('log')->info('店铺下单',$request->all());
        app('db')->beginTransaction();
        $outSn = "";
        try {
            // $this->processFormId($request);
            // if($request->filled('form_id')) {
            //     ShopWeappFormId::create([
            //         'form_id'   => $request->form_id,
            //         'user_id'   => Auth::user()->id
            //     ]);
            // }

            $cart = new CartService();

            if ($request->verify_money != $cart->name($this->getWhoisCart())->total($request->id)) {
                info('验证价格Debug', [
                    'key' => $this->getWhoisCart(),
                    'total' => $cart->name($this->getWhoisCart())->all()->toArray(),
                ]);

                throw new \Exception("下单金额不一致", 1);
            }

            if(0 === app('cart')->name($this->getWhoisCart())->countWithChecked($request->id))
            {
                throw new \Exception("购物车不能为空", 1);
            }

            $data = new BaseRequests;

            $data->express_code  = "";
            $data->remark           = $request->input('remark', '');
            $data->shop_id          = $request->shop->id;
            $data->shop_user_id     = Auth::user()->id;
            $data->warehouse_id     =  $request->shop->warehouse_id;
            $data->order_type       = OrderType::where('warehouse_id', $request->shop->warehouse_id)->oldest()->first()->id??0;
            $data->shop_remark      = "";
            $data->express_num      = "";
            $data->sale_currency    =  $request->shop->default_currency;


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
            foreach(app('cart')->name($this->getWhoisCart())->all($request->id) as $row)  {
                $orderItem[] = [
                    'relevance_code'    =>  $row->relevance_code,
                    'pic'               =>  $row->pic,
                    'num'               =>  $row->qty,
                    'sale_price'        =>  $row->price,
                ];
            }


            $data->goods_data = collect($orderItem);

            $orderResult = app('order')->setSource($request->shop->name_cn)->create($data, $request->shop->owner_id);
            app('db')->commit();

            $outSn =  $orderResult->out_sn;

            app('cart')->name($this->getWhoisCart())->removeBy($request->id);

            event(new CartCheckouted($orderResult));

        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('下单失败',['msg'=>$e->getMessage()]);
            return formatRet(500, '下单失败:'.$e->getMessage());
        }


        return formatRet(0,'下单成功',[
            'out_sn'  =>  $outSn
        ]);
    }
}
