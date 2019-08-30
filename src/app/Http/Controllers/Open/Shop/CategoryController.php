<?php
/**
 * 店铺分类
 */

namespace App\Http\Controllers\Open\Shop;
use App\Http\Requests\BaseRequests;
use App\Rules\PageSize;
use App\Models\Category;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{

    /**
     * 分类首页
     **/
    public function list(BaseRequests $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize(),
            'is_enabled'   => 'boolean',
        ]);

        $categories = Category::OfWarehouse($request->shop->warehouse_id)
                    ->where('is_enabled', 1)
                    ->whereRaw("exists (select id from shop_product where category_id = category.id and shop_id = {$request->shop->id})")
                    ->orderBy('id','ASC')
                    ->paginate(
                        $request->input('page_size',50),
                        ['id', 'name_cn']
                    );
        return formatRet(0, '', $categories->toArray());
    }
}
