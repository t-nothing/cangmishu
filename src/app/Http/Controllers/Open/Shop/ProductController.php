<?php
/**
 * 商品分类
 */

namespace App\Http\Controllers\Open\Shop;
use App\Http\Requests\BaseRequests;
use App\Rules\PageSize;
use App\Models\ShopProduct;

class ProductController extends Controller
{

    /**
     * 商品首页
     **/
    public function index(BaseRequests $request, int $catId = 0)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize(),
            'is_enabled'   => 'boolean',
        ]);

        $categories = Category::OfWarehouse(Auth::shopWarehouseId())
                    ->where('is_enabled', 1)
                    ->orderBy('id','ASC')
                    ->paginate($request->input('page_size',10));

        return formatRet(0, '', $categories->toArray());
    }

    /**
     * 分类详细
     **/
    public function show(BaseRequests $request, int $id)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize(),
            'is_enabled'   => 'boolean',
        ]);

        $categories = Category::OfWarehouse(Auth::shopWarehouseId())
                    ->where('is_enabled', 1)
                    ->orderBy('id','ASC')
                    ->paginate($request->input('page_size',10));

        return formatRet(0, '', $categories->toArray());
    }
}
