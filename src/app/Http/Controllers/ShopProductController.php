<?php

namespace App\Http\Controllers;
use App\Models\Shop;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ShopProduct;
use App\Models\ShopProductSpec;
use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateShopProductRequest;
use App\Http\Requests\UpdateShopProductRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Validator;
use Illuminate\Support\Facades\Storage;


class ShopProductController extends Controller
{
    /**
     * 店铺商品列表
     */
    public function index(BaseRequests $request, int $shopId)
    {
        app('log')->info('店铺商品列表',$request->all());
        $this->validate($request,[
            'created_at_b'      => 'date_format:Y-m-d',
            'created_at_e'      => 'date_format:Y-m-d|after_or_equal:created_at_b',
            'keywords'          => 'string|max:255',
        ]);
        $batchs =   ShopProduct::leftJoin('product', 'shop_product.product_id', '=', 'product.id')
            ->with("specs")
            ->where('shop_id', $shopId)
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
            ->latest()->paginate($request->input('page_size',10), [
                'shop_product.id',
                'shop_product.name_cn',
                'shop_product.name_en',
                'shop_product.sale_price',
                'shop_product.is_shelf',
                'shop_product.pics',
                'shop_product.remark',
                'shop_product.created_at',
                'shop_product.updated_at',
                'shop_product.weapp_qrcode',
            ]);

            $re = $batchs->toArray();

//
            $data = collect($re['data'])->map(function($v){
                $v['pics'] = json_decode($v['pics'], true);
                unset($v['batch_products']);
                return $v;
            })->toArray();
            $re['data'] = $data;
        return formatRet(0,'',$re);
    }

    /**
     * 更新单个商品
     **/
    function update(UpdateShopProductRequest $request, int $shopId, int  $id)
    {
 
        app('db')->beginTransaction();
        try
        {
            $shopProduct = $request->getShopProduct();
            
            $shopProduct->is_shelf      = 1;
            $shopProduct->name_cn       = $request->name_cn??"";
            $shopProduct->remark        = $request->input("name_en", $request->name_cn);
            $shopProduct->remark        = $request->remark??"";
            $shopProduct->pics          = json_encode($request->pics, true);
            $shopProduct->descs         = json_encode($request->descs, true);
            
            foreach ($request->specs as $s) {
                $spec = ShopProductSpec::where("shop_product_id", $id)->where("id", $s["id"])->firstOrFail();
                $spec->name_cn      = $s["name_cn"];
                $spec->name_en      = $s["name_en"]??$s["name_cn"];
                $spec->is_shelf      = $s["is_shelf"]??1;
                $spec->sale_price   = $s["sale_price"];
                $spec->save();
            }
            $shopProduct->sale_price    = $request->specs[0]["sale_price"];
            $shopProduct->save();
            
            app('db')->commit();
        }
        catch (\Exception $e)
        {
            app('db')->rollback();
            app('log')->error('修改商品失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"修改商品失败");
        }

        return formatRet(0);
    }

    /**
     * 选择商品
     **/
    function store(CreateShopProductRequest $request, int $shopId)
    {
        $dbProducts = Product::ofWarehouse($request->warehouse_id)
            ->where('owner_id',app('auth')->ownerId())
            ->whereIn('id', $request->products)
            ->get()->keyBy('id');

        $dbIdArr = $dbProducts->pluck('id')->toArray();

        //
        $diffArr = array_diff($request->products, $dbIdArr);
        if(count($diffArr))
        {
            app('log')->info('店铺商品新增',$request->all());
            return formatRet(500,"新增商品失败,商品信息不存在");
        }

        app('db')->beginTransaction();
        try
        {
            foreach ($request->products as $key => $product_id) {

                $exist = ShopProduct::where('shop_id', $shopId)->where('product_id', $product_id)->count();
                if($exist) continue;

                $productInfo = $dbProducts[$product_id];
                $productInfo->load("specs");
                $shopProduct                = new ShopProduct;
                $shopProduct->shop_id       = $shopId;
                $shopProduct->product_id    = $product_id;
                $shopProduct->name_cn       = $productInfo["name_cn"];
                $shopProduct->name_en       = $productInfo["name_en"];
                $shopProduct->is_shelf      = 1;
                $shopProduct->category_id   = $productInfo["category_id"];
                $shopProduct->remark        = $productInfo["remark"]??"";
                $shopProduct->sale_price    = $productInfo->specs[0]["sale_price"];
                if(!empty($productInfo["photos"]))
                {
                    $shopProduct->pics          = json_encode([$productInfo["photos"]], true);
                }

                $shopProduct->save();

                $filePath = storage_path('/app/public/weapp/') ;
                //如果图片不存在才会生成
                if(!file_exists($filePath.sprintf("%s-%s.png", $request->modelData->domain, $shopProduct->id)))
                {
                    $app = app('wechat.mini_program');
                    $response = $app->app_code->get('pages/index/product_detail/product_detail?shop='.$shopId.'&product='.$shopProduct->id);

                    if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {

                        
                        $filename = $response->saveAs($filePath, sprintf("%s-%s.png", $request->modelData->domain, $shopProduct->id));

                        $url = Storage::url('weapp/'.$filename);
                        $shopProduct->weapp_qrcode   = app('url')->to($url) ;
                        $shopProduct->save();
                    }
                }

                $specs = [];
                foreach ($productInfo->specs as $spec) {

                    $specs[] = new ShopProductSpec([
                        'shop_id'           =>  $shopId,
                        'shop_product_id'   =>  $shopProduct->id,
                        'name_cn'           =>  $spec->name_cn,
                        'name_en'           =>  $spec->name_en,
                        'product_id'        =>  $spec->product_id,
                        'spec_id'           =>  $spec->id,
                        'sale_price'        =>  $spec->sale_price,
                        'is_shelf'          =>  1,
                    ]);
                }
                
                
                $shopProduct->specs()->saveMany($specs);
            }

            app('db')->commit();
        }
        catch (\Exception $e)
        {
            app('db')->rollback();
            app('log')->error('新增商品失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"新增商品失败");
        }

        return formatRet(0);
    }

    /**
     * 删除商品
     */
    public function destroy(BaseRequests $request, int $shopId, $ids)
    {
        $idArr = explode(",", $ids);
        $idArr = collect($idArr)->map(function ($item, $key) {
            return intval($item) ;
        });


        try {
            $shopProducts = ShopProduct::with("shop")->whereIn('id', $idArr->all())->get();
            app('db')->beginTransaction();
            foreach ($shopProducts as $key => $shopProduct) {

                if ( !$shopProduct->shop || $shopProduct->shop->owner_id != Auth::id() || $shopProduct->shop->id != $shopId){
                    return formatRet(500,'用户不存在或无权限编辑');
                }
                
                $shopProduct->specs()->delete();
                $shopProduct->delete();
            }
            
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
    public function show(BaseRequests $request, int $shopId, int  $id)
    {
         
        $shopProduct = ShopProduct::with("shop")->findOrFail($id);

        if ( !$shopProduct->shop || $shopProduct->shop->owner_id != Auth::id() || $shopProduct->shop->id != $shopId){
            return formatRet(500,'用户不存在或无权限编辑');
        }
        $shopProduct->load("specs");

        foreach ($shopProduct->specs as $key => $value) {

            $value->origin_sale_price = ProductSpec::getSalePrice($value->spec_id);
        }

        $shopProduct->pics = json_decode($shopProduct->pics, true)??[];
        $shopProduct->descs = json_decode($shopProduct->descs, true)??[];

        return formatRet(0,"成功",$shopProduct->toArray());
    }

    /**
     * 上下架商品
     */
    public function onShelf(BaseRequests $request, int $shopId)
    {

        $this->validate($request,[
            'is_shelf'          => 'required|boolean',
            'id'                => 'required|array',
            'id.*'              => 'required|int|min:1',
        ]);

        $shopProducts = ShopProduct::with("shop")->whereIn('id', $request->id)->get();

        
        
        try {
            app('db')->beginTransaction();
            foreach ($shopProducts as $key => $shopProduct) {
                if ( !$shopProduct->shop || $shopProduct->shop->owner_id != Auth::id() || $shopProduct->shop->id != $shopId){
                    return formatRet(500,'用户不存在或无权限编辑');
                }

                $shopProduct->specs()->update(['is_shelf'=>$request->is_shelf]);
                $shopProduct->is_shelf = $request->is_shelf;
                $shopProduct->save();
            }
           
            app('db')->commit();

        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500,'操作商品失败');
        }
        return formatRet(0);
    }


}