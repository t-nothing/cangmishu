<?php
/**
 * 店铺
 */

namespace App\Http\Controllers\Open\Shop;
use App\Http\Requests\BaseRequests;
use App\Http\Controllers\Controller;
use App\Rules\PageSize;
use App\Models\Shop;

class ShopController extends Controller
{

    /**
     * 店铺详细
     **/
    public function show(BaseRequests $request)
    {
        $request->shop->setVisible(['name', 'remark', 'logo', 'banner_background']);
        return formatRet(0, '', $request->shop->toArray());
    }
}
