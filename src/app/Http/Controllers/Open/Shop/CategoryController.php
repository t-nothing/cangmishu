<?php
/**
 * 店铺分类
 */

namespace App\Http\Controllers\Open\Shop;
use App\Http\Requests\BaseRequests;
use App\Rules\PageSize;
use App\Models\Category;

class CategoryController extends Controller
{

    /**
     * 分类首页
     **/
    public function index(BaseRequests $request)
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
