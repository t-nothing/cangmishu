<?php
/**
 * 商品分类
 */

namespace App\Http\Controllers\Open\Shop;

use App\Http\Requests\BaseRequests;
use App\Models\ShopUser;
use App\Rules\PageSize;
use App\Http\Controllers\Controller;
use App\Models\ShopProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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
            ->with(['specs', 'specs.productSpec'])
            ->when($catId, function($q) use($catId) {
                return $q->where('product.category_id', $catId);
            })
            ->where('shop_id', $request->shop->id)
            ->where('shop_product.is_shelf', 1)
            ->when($request->filled('keywords'),function ($q) use ($request) {
                return $q->hasKeyword($request->input('keywords'));
            })
            ->latest()->paginate($request->input('page_size',10), [
                'shop_product.id',
                'product.name_cn',
                'product.name_en',
                'product.total_stock_num',
                'shop_product.sale_price',
                'shop_product.is_shelf',
                'shop_product.pics',
                'shop_product.remark',
                'shop_product.created_at',
                'shop_product.updated_at',
            ]);

        return $this->convertProductList($dataList, $request);
    }

    /**
     * 分类详细
     **/
    public function show(BaseRequests $request, $id)
    {
        $id = intval($id);
        /** @var ShopProduct $shopProduct */
        $shopProduct = ShopProduct::with(['product:id,total_stock_num', 'shop', 'collectUsers'])->findOrFail($id);

        if ( !$shopProduct || !$shopProduct->shop ){
            return formatRet(404,'商品不存在', 404);
        }
        $shopProduct->load(['specs', 'specs.productSpec']);

        $shopProduct->currency = $request->shop->currency;
        $shopProduct->is_collect = $shopProduct->isCollect();
        $shopProduct->total_stock_num = $shopProduct->product->total_stock_num ?? 0;

        unset($shopProduct['collectUsers'], $shopProduct['product']);

        foreach ($shopProduct['specs'] as &$spec) {
            $spec['total_stock_num'] = $spec['productSpec']['total_stock_num'];
            unset($spec['productSpec']);
        }

        return formatRet(0,"成功", $shopProduct->toArray());
    }

    /**
     * @param  int  $id
     * @return JsonResponse
     */
    public function collect(int $id)
    {
        $shopProduct = ShopProduct::query()->findOrFail($id);

        /** @var ShopUser $user */
        $user = auth('shop')->user();

        $user->collectShopProduct()->attach($shopProduct->getKey());

        return formatRet(0,'成功');
    }

    /**
     * @param  int  $id
     * @return JsonResponse
     */
    public function unCollect(int $id)
    {
        $shopProduct = ShopProduct::query()->findOrFail($id);

        /** @var ShopUser $user */
        $user = auth('shop')->user();

        $user->collectShopProduct()->detach($shopProduct->getKey());

        return formatRet(0,'成功');
    }

    /**
     * @param  BaseRequests  $request
     * @return JsonResponse
     */
    public function collectionList(BaseRequests $request)
    {
        /** @var ShopUser $user */
        $user = auth('shop')->user();

        $collectIds = $user->collectShopProduct->modelKeys();

        $dataList =   ShopProduct::query()
            ->leftJoin('product', 'shop_product.product_id', '=', 'product.id')
            ->with(['specs', 'specs.productSpec'])
            ->where('shop_id', $request->shop->id)
            ->where('shop_product.is_shelf', 1)
            ->whereIn('shop_product.id', $collectIds)
            ->when($request->filled('keywords'),function ($q) use ($request){
                return $q->hasKeyword($request->input('keywords'));
            })
            ->latest()->paginate($request->input('page_size',10), [
                'shop_product.id',
                'product.name_cn',
                'product.name_en',
                'product.total_stock_num',
                'shop_product.sale_price',
                'shop_product.is_shelf',
                'shop_product.pics',
                'shop_product.remark',
                'shop_product.created_at',
                'shop_product.updated_at',
            ]);

        return $this->convertProductList($dataList, $request);
    }

    /**
     * @param  LengthAwarePaginator  $dataList
     * @param  BaseRequests  $request
     * @return JsonResponse
     */
    protected function convertProductList(LengthAwarePaginator $dataList, BaseRequests $request): JsonResponse
    {
        $re = $dataList->toArray();

        $currency = $request->shop->currency;

        $data = collect($re['data'])->map(function ($v) use ($currency) {
            $v['currency'] = $currency;

            $v['specs'] = collect($v['specs'])->map(function ($spec) {
                $spec['total_stock_num'] = $spec['product_spec']['total_stock_num'];
                unset($spec['product_spec']);

                return $spec;
            })->all();

            return $v;
        })->toArray();

        $re['data'] = $data;

        return formatRet(0, '', $re);
    }

    /**
     * @param  string  $keyword
     * @return bool
     */
    protected function pushKeyword(string $keyword)
    {
        /** @var Collection $keywords */
        $keywords = Cache::get($this->keywordKeyName(), collect([]));

        $keywords = $keywords->merge([$keyword]);

        return Cache::forever($this->keywordKeyName(), $keywords);
    }

    /**
     * @return mixed
     */
    protected function getKeywords()
    {
        $data = Cache::get($this->keywordKeyName(), collect([]));

        return formatRet(0, '操作成功', $data);
    }

    /**
     * @return string
     */
    protected function keywordKeyName()
    {
        return 'UserKeywords_'.auth('shop')->id();
    }
}
