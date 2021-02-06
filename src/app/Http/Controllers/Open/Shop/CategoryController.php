<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
                    ->whereRaw("exists (select id from shop_product where category_id = category.id and shop_id = {$request->shop->id} and shop_product.is_shelf = 1)")
                    ->orderBy('id','ASC')
                    ->paginate(
                        $request->input('page_size',50),
                        ['id', 'name_cn']
                    );
        return formatRet(0, '', $categories->toArray());
    }
}
