<?php
namespace  App\Services;

use App\Exceptions\BusinessException;
use App\Models\Recount;
use App\Models\RecountStock;
use App\Models\ProductStockLocation;
use DB;
use App\Events\StockLocationAdjust;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;

class RecountService
{
    protected  $warehouse;

    public function __construct()
    {
        $this->warehouse = app('auth')->warehouse();
    }

    /**
     * 创建盘点单
     */
    public function create($data)
    {
        //校验stock
        if(isset($data['stock']))
        {
            $lock = NULL;
            try {
                $lock = Cache::lock(sprintf("recountStockLocksV1:%s:%d", $data["warehouse_id"], strtotime(date("Y-m-d H"))));

                //加一个锁防止并发
                if ($lock->get()) {

                    $arr = [];
                    $op = [];

                    $totalQty = 0;
                    $totalOrginQty = 0;
                    foreach ($data['stock'] as $key => $v) {

                        $stockLoation = ProductStockLocation::where("warehouse_id", $data["warehouse_id"])->where("id", $v["id"])->first();
                        if(! $stockLoation){
                            if ($stockLocation = ProductStockLocation::where("id", $v["id"])->first()) {
                                throw new BusinessException("SKU：{$stockLocation->stock->sku} 不属于当前仓库，请删除后提交");
                            }

                            throw new \Exception("未找到库存ID:".$v["id"], 1);

                        }

                        $stockLoation->load("stock");

                        $op[] = [
                            'model' =>  $stockLoation,
                            'qty'   =>  $v["num"],
                            'diff' => $v['num'] - $stockLoation->shelf_num,
                        ];

                        app('log')->info('库存盘点,修改库存为', [
                            'qty'       =>  $v["num"],
                            'id'  =>  $stockLoation->id
                        ]);

                        $totalQty += $v["num"];
                        $totalOrginQty += $stockLoation->shelf_num;

                        $arr[] = new RecountStock([
                            'stock_location_id'     =>  $stockLoation->id,
                            'name_cn'               =>  $stockLoation->stock->product_name_cn,
                            'name_en'               =>  $stockLoation->stock->product_name_en,
                            'relevance_code'        =>  $stockLoation->stock->spec->relevance_code,
                            'stock_sku'             =>  $stockLoation->stock->sku,
                            'shelf_num_orgin'       =>  $stockLoation->shelf_num,
                            'shelf_num_now'         =>  $v["num"],
                            'total_purcharse_orgin' =>  $stockLoation->stock->purchase_price * $stockLoation->shelf_num,
                            'total_purcharse_now'   =>  $stockLoation->stock->purchase_price * $v["num"],
                            'status'                =>  1,
                            'location_code'         =>  $stockLoation->warehouse_location_code??'',
                            'location_id'           =>  $stockLoation->warehouse_location_id??0,
                        ]);
                    }

                    $model = new Recount;
                    $model->recount_no = Recount::no();

                    $model->status = 1;
                    $model->diff_count = $totalQty - $totalOrginQty;
                    $model->remark = $data["remark"];
                    $model->warehouse_id = $data["warehouse_id"];
                    $model->owner_id = $data["owner_id"];
                    $model->save();
                    $model->stocks()->saveMany($arr);

                    ## 第二步调整库存
                    foreach ($op as $key => $v) {
                        event(new StockLocationAdjust($v["model"], $v["qty"],[
                            'order_sn'  =>  $model->recount_no,
                            'remark'    =>  $data["remark"],
                            'diff' => $v['diff'],
                        ]));
                    }
                    $lock->release();
                }
            } catch (BusinessException $exception) {
                $lock->release();
                throw $exception;
            } catch(\Exception $ex) {
                $lock->release();
                throw new \Exception($ex->getMessage(), 1);
            }
        }
    }


    /**
     * 根据规格ID得到位置
     *
     */
    public function getLocationBySpec($request){


        $stockLocations = ProductStockLocation::with('stock')
            ->ofWarehouse($request["warehouse_id"])
            ->whereIn('spec_id', $request["spec"])
            ->get();

        $arr = [];
        foreach ($stockLocations as $key => $stockLoation) {

            $arr[] = [
                    'id'                    =>  $stockLoation->id,
                    'name_cn'               =>  $stockLoation->stock->product_name_cn,
                    'name_en'               =>  $stockLoation->stock->product_name_en,
                    'relevance_code'        =>  $stockLoation->stock->spec->relevance_code,
                    'stock_sku'             =>  $stockLoation->stock->sku,
                    'shelf_num_orgin'       =>  $stockLoation->shelf_num,
                    'shelf_num_now'         =>  $stockLoation->shelf_num,
                    'total_purcharse_orgin' =>  $stockLoation->stock->purchase_price * $stockLoation->shelf_num,
                    'total_purcharse_now'   =>  $stockLoation->stock->purchase_price * $stockLoation->shelf_num,
                    'status'                =>  1,
                    'location_code'         =>  $stockLoation->warehouse_location_code,
                    'location_id'           =>  $stockLoation->warehouse_location_id,
                ];
        }

        return $arr;
    }
}
