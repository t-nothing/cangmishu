<?php
/**
 * 店铺订单
 */

namespace App\Http\Controllers\Open\Shop;

class OrderController extends Controller
{
    /**
     * 我的订单列表
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
                    ->orderBy('id','ASC')
                    ->paginate(
                        $request->input('page_size',50),
                        ['id', 'name_cn']
                    );

        return formatRet(0, '', $categories->toArray());
    }

    /**
     * 在线下单
     */
    public function store(CreateOrderRequest $request)
    {
        app('log')->info('新增出库单',$request->all());
        app('db')->beginTransaction();
        try {
            app('order')->create($request);
            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('新增出库单失败',['msg'=>$e->getMessage()]);
            return formatRet(500, '出库单新增失败');
        }
        return formatRet(0,'出库单新增成功');
    }

    /**
     * 订单详细
     */
    public function show(CreateOrderRequest $request)
    {
        app('log')->info('新增出库单',$request->all());
        app('db')->beginTransaction();
        try {
            app('order')->create($request);
            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('新增出库单失败',['msg'=>$e->getMessage()]);
            return formatRet(500, '出库单新增失败');
        }
        return formatRet(0,'出库单新增成功');
    }
}
