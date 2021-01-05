<?php
/**
 * @Author: h9471
 * @Created: 2020/12/29 10:57
 */

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ReceiverAddress;
use App\Models\Shop;
use App\Models\ShopSenderAddress;

class MiniProgramIndexController extends Controller
{
    public function statistics()
    {
        $warehouseId = auth()->user()->default_warehouse_id;

        $wait_confirm = Order::query()
            ->where('warehouse_id', $warehouseId)
            ->where('status', Order::STATUS_DEFAULT)
            ->count();

        $wait_ship = Order::query()
            ->where('warehouse_id', $warehouseId)
            ->where('status', Order::STATUS_WAITING)
            ->count();

        $wait_shelf = Batch::query()->where('warehouse_id', $warehouseId)
            ->where('status', '=', Batch::STATUS_PREPARE)
            ->count();

        $product = Product::query()
            ->where('warehouse_id', $warehouseId)
            ->count('id');

        $stock_warning = ProductSpec::query()
            ->where('product_spec.warehouse_id', $warehouseId)
            ->join('product as p', 'product_spec.product_id', '=', 'p.id')
            ->join('category as c', 'c.id', '=', 'p.category_id')
            ->whereRaw('product_spec.total_stock_num <= c.warning_stock and c.warning_stock >0')
            ->count();

        $shops = Shop::query()->where('warehouse_id', $warehouseId)->select('id')->get();

        $shop = $shops->count();

        $shopUser = ReceiverAddress::query()
            ->where('owner_id', auth()->id())
            ->count();

        $supplier = Distributor::query()
            ->where('user_id', auth()->id())
            ->count();

        $data = compact('wait_confirm',
            'wait_ship',
            'wait_shelf',
            'product',
            'stock_warning',
            'shop',
            'shopUser',
            'supplier');

        return success($data);
    }
}
