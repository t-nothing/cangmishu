<?php
/**
 * 店铺
 */

namespace App\Http\Controllers\Open\Shop;
use App\Http\Requests\BaseRequests;
use App\Http\Controllers\Controller;
use App\Rules\PageSize;
use App\Models\Shop;
use App\Models\ShopWeappFormId;

class ShopController extends Controller
{

    /**
     * 店铺详细
     **/
    public function show(BaseRequests $request)
    {
        $request->shop->setVisible([
            'name',
            'remark',
            'logo',
            'banner_background',
            'senderAddress',
            'currency',
        ]);

        return formatRet(0, '', $request->shop->toArray());
    }

    /**
     * 推荐店铺列表
     **/
    public function index(BaseRequests $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize(),
        ]);

        $list = Shop::where('is_closed', 0)
                    ->where('sort_num', '>', 0) //只显示推荐的
                    ->orderBy('sort_num','desc')
                    ->orderBy('id','ASC')
                    ->paginate(
                        $request->input('page_size',50),
                        ['id', 'name_cn', 'logo', 'remark_cn', 'weapp_qrcode']
                    );

        return formatRet(0, '', $list->toArray());
    }
}
