<?php
/**
 * 开放库存查询.
 */

namespace App\Http\Controllers\Open\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\BaseRequests;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use App\Models\ProductStockLocation;
use App\Models\WarehouseLocation;

class StockController extends Controller
{

    /**
     * 根据SKU查询库存
     */
    public function spec(BaseRequests $request){

        $this->validate($request,[
            'code'     => 'required|string|max:1000',
        ]);

        $skuArr = explode(",", $request->code);
        if(count($skuArr) > 20) {
            return formatRet(500, trans("message.openStockSearchOverThanMax", ["num"=>20]));
        }

        $dataList = ProductSpec::with('product:id,name_cn')->whereIn('relevance_code', $skuArr)->where('warehouse_id', Auth::warehouseId())->latest()->select(['product_id','total_stock_num as qty', 'relevance_code as spec_sku', 'total_stockin_times as stockin_times', 'total_stockout_times as stockout_times'])->paginate($request->input('page_size',10))->toArray();

        return formatRet(200, trans("message.success"), $dataList);
      
    }

    /**
     * 根据货位查询库存
     */
    public function location(BaseRequests $request){

        $this->validate($request,[
            'code'     => 'required|string|max:1000',
        ]);


        $warehouseLocation = WarehouseLocation::where('code', $request->code)
            ->where('warehouse_id', Auth::warehouseId())
            ->first();

        if(!$warehouseLocation) {
            return formatRet(500, trans("message.warehouseLocationNotExistExt", ["code"=>$code]));
        }

        $dataList = ProductStock::where('warehouse_location_id', $warehouseLocation->id)->latest()->select(['sku as stock_sku','shelf_num as qty', 'relevance_code as spec_sku','stock_num as qty'])->paginate($request->input('page_size',10))->toArray();

        return formatRet(0, '', $dataList);
      
    }

}