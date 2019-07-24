<?php

namespace App\Http\Controllers;
use App\Http\Requests\BaseRequests;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class ShopProductController extends Controller
{
    /**
     * 店铺商品列表
     */
    public function index(BaseRequests $request, int $id)
    {
        app('log')->info('店铺商品列表',$request->all());
        $this->validate($request,[
            'created_at_b'      => 'date_format:Y-m-d',
            'created_at_e'      => 'date_format:Y-m-d|after_or_equal:created_at_b',
            'keywords'          => 'string|max:255',
        ]);
        $batchs =   ShopProduct::leftJoin('product', 'shop_product.product_id', '=', 'product.product_id')
            ->ofWarehouse($request->input('warehouse_id'))
            ->where('shop_id', $id)
            ->where('owner_id',Auth::ownerId())
            ->when($request->filled('created_at_b'),function ($q) use ($request){
                return $q->where('shop_product.created_at', '>', strtotime($request->input('created_at_b')));
            })
            ->when($request->filled('created_at_e'),function ($q) use ($request){
                return $q->where('shop_product.created_at', '<', strtotime($request->input('created_at_e')));
            })
            ->when($request->filled('keywords'),function ($q) use ($request){
                return $q->hasKeyword($request->input('keywords'));
            })
            ->latest()->paginate($request->input('page_size',10));

            $re = $batchs->toArray();

//
            $data = collect($re['data'])->map(function($v){
                unset($v['batch_products']);
                return $v;
            })->toArray();
            $re['data'] = $data;
        return formatRet(0,'',$re);
    }

    /**
     * 更新销售价格
     **/
    function updateSalePrice(BaseRequests $request,int  $id)
    {
        $this->validate($request, [
            'sale_price'              => 'required|float|min:0.01',
        ]);

        app('db')->beginTransaction();
        try
        {

            $shopProduct = ShopProduct::find($id)->with('shop');

            if (! $shopProduct || $shopProduct->shop || $shopProduct->shop->owner_id != Auth::id()){
                return formatRet(500,'用户不存在或无权限编辑');
            }

            $shopProduct->sale_price = $request->sale_price;
            $shop->save();

            app('db')->commit();
        }
        catch (\Exception $e)
        {
            app('db')->rollback();
            app('log')->error('修改商品价格失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"修改商品价格失败");
        }

        return formatRet(0);
    }

    /**
     * 同步商品库
     **/
    function syncProduct(BaseRequests $request,int  $id)
    {
        $shop = ShopProduct::find($id);
        if(!$shop)
        {

        }


    }

    /**
     * 选择商品
     **/
    function chooseProduct(BaseRequests $request,int  $id)
    {
        $this->validate($request, [
            'products'                  => 'required|array|max:100',
            'products.*'                => 'required|int|min:1',
        ]);

        $dbProducts = Product::ofWarehouse($request->warehouse_id)
            ->where('owner_id',app('auth')->ownerId())
            ->whereIn('id', $request->products)
            ->select('id')
            ->get()->pluck('id')->toArray();

        //
        $diffArr = array_diff($request->products, $dbProducts);
        if($diffArr)
        {

        }

        app('db')->beginTransaction();
        try
        {
            ShopProduct::updateOrCreate(
                [
                    "shop_id"       =>  $id,
                    "product_id"    =>  $id,
                ],
                [
                    'product_id'    =>  $request->product_id,
                    'shop_id'       =>  $request->shop_id,
                    'sale_price'    =>  $request->sale_price,
                    'is_shelf'      =>  $request->is_shelf,
                ]
            );

            app('db')->commit();
        }
        catch (\Exception $e)
        {
            app('db')->rollback();
            app('log')->error('新增店铺失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"新增店铺失败");
        }

        return formatRet(0);
    }

    /**
     * 删除商品
     */
    public function destroy(BaseRequests $request,$id)
    {

        $shopProduct = ShopProduct::find($id)->with('shop');

        if (! $shopProduct || $shopProduct->shop || $shopProduct->shop->owner_id != Auth::id()){
            return formatRet(500,'用户不存在或无权限编辑');
        }

        
        app('db')->beginTransaction();
        try {
            $shopProduct->delete();
            app('db')->commit();

        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500,'删除店铺商品失败');
        }
        return formatRet(0);
    }

    /**
     * 商品详细
     */
    public function show(BaseRequests $request,$id)
    {
        $shop = Shop::find($id);

        if (! $shop || $shop->owner_id != Auth::id()){
            return formatRet(500,'店铺不存在或无权限编辑');
        }

        $shop->load("senderAddress","paymentMethod");

        return formatRet(0,"成功",$shop->toArray());
    }

}