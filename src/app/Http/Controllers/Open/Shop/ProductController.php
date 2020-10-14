<?php
/**
 * 商品分类
 */

namespace App\Http\Controllers\Open\Shop;
use App\Http\Requests\BaseRequests;
use App\Rules\PageSize;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Product;
use App\Models\ShopProduct;
use App\Models\ShopProductSpec;

class ProductController extends Controller
{

    /**
     * 商品首页
     **/
    public function list(BaseRequests $request,  $catId = 0)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize(),
        ]);

        $catId = intval($catId);
        $dataList =   ShopProduct::leftJoin('product', 'shop_product.product_id', '=', 'product.id')
            ->with("specs")
            ->when($catId, function($q) use($catId) {
                return $q->where('product.category_id', $catId);
            })
            ->where('shop_id', $request->shop->id)
            ->where('shop_product.is_shelf', 1)
            ->when($request->filled('keywords'),function ($q) use ($request){
                return $q->hasKeyword($request->input('keywords'));
            })
            ->latest()->paginate($request->input('page_size',10), [
                'shop_product.id',
                'product.name_cn',
                'product.name_en',
                'shop_product.sale_price',
                'shop_product.is_shelf',
                'shop_product.pics',
                'shop_product.remark',
                'shop_product.created_at',
                'shop_product.updated_at',
            ]);

            $re = $dataList->toArray();

//      
            $currency = $request->shop->currency;
            $data = collect($re['data'])->map(function($v) use($currency){
                $v['currency'] = $currency;
                return $v;
            })->toArray();
            $re['data'] = $data;
        return formatRet(0,'',$re);
    }

    /**
     * 分类详细
     **/
    public function show(BaseRequests $request, int $id)
    {
        $shopProduct = ShopProduct::with("shop")->findOrFail($id);

        if ( !$shopProduct || !$shopProduct->shop ){
            return formatRet(404,'商品不存在', 404);
        }
        $shopProduct->load("specs");
        $shopProduct->currency = $request->shop->currency;

        return formatRet(0,"成功",$shopProduct->toArray());
    }
}
