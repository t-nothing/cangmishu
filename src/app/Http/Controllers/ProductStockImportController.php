<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateBatchRequest;
use App\Models\Batch;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use App\Models\ProductStockLocation;
use App\Models\WarehouseLocation;
use App\Imports\ProductSpecStockImport;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * 商品库存导入
 */
class ProductStockImportController extends  Controller
{
    /**
     * 检查库存导入
     */
    public function upload(BaseRequests $request)
    {
        app('log')->info('检查库存导入', $request->all());

        $this->validate($request,[
            'file' =>'required|file'
        ]);

        $newResult = [];
        try {
            $productImport = new ProductSpecStockImport();
            $resultAll = app('excel')->toArray($productImport, $request->file('file'), 'UTF-8');
            $result = collect($resultAll[0])->filter(function ($value) {
                return isset($value['规格SKU']) && $value['规格SKU']!='' ;
            })->all();

            if($result) {
                foreach ($result as $key => $value) {
                    $newResult[] = [
                        "relevance_code"        =>  sprintf("%s", $value["规格SKU"])??"",
                        "need_num"              =>  intval($value["规格数量"]??0),
                        "purchase_price"        =>  floatval($value["进货价格"]??0),
                        "need_num"              =>  intval($value["规格数量"]??0),
                        "location_code"         =>  $value["上架货位"]??"",
                        "production_batch_number"         =>  $value["生成批次号"]??"",
                        "ean"                   =>  $value["EAN"]??"",
                        "expiration_date"       =>  $value["保质期至"]??"",
                        "best_before_date"      =>  $value["最佳食用期"]??"",
                        "remark"                =>  $value["备注"]??"",
                        "auto_create_location"  => 0
                    ];
                }
            }

            return formatRet(0, '', $newResult);

        } catch (ValidationException $e) {
            $errorMessage = array_values($e->errors())[0][0];
            return formatRet(500, '导入结束,数据验证未通过：'. $errorMessage);
        } catch(\Exception $exception) {

            app('log')->error('货品导入失败', ["msg" => $exception->getMessage()]);
            return formatRet(500, '导入失败');
        }


    }

    /**
     * 模板下载
     */
    public function template(BaseRequests $request) {

        return formatRet(0, '', [
            'url'   =>  asset('template/库存导入模板.xlsx'),
            'date'  =>  '2021-03-11'
        ]);

    }

    /**
     * 检查内容
     **/
    public function check(BaseRequests $request) {
        app('log')->info('提交的单个信息', $request->all());
        $this->validate($request,[
            'relevance_code'            => 'required',
            'need_num'                  => 'required|integer|min:1',
            'location_code'             => 'required',
            'ean'                       => 'sometimes|string|max:255',
            'expiration_date'           => 'date_format:Y-m-d|nullable',
            'best_before_date'          => 'date_format:Y-m-d|nullable',
            'production_batch_number'   => 'string|max:255|nullable',
            'purchase_price'            => 'required|numeric|min:0|max:99999',
            'auto_create_location'      => 'required|integer|min:0|max:1',
        ]);

        $spec = ProductSpec::where('owner_id', app('auth')->ownerId())
                ->where("warehouse_id", app('auth')->warehouse()->id)
                ->where('relevance_code', $request->relevance_code)
                ->first();

        //先判断SKU是否库存
        if (! $spec) {
            return formatRet(500, trans("message.productSpecNotExists"), [ "col" => 1 ], 500);
        }

        //再判断货位存不存在
        if (!$request->allow_not_exists && ! $location = WarehouseLocation::where('owner_id', app('auth')->ownerId())->where("warehouse_id", app('auth')->warehouse()->id)->where('code', $request->location_code)->first()) {
            return formatRet(500, trans("message.warehouseLocationNotExist"), [ "col" => 4 ], 500);
        }

        $newResult = [
            'ean'                               =>  $request->ean,
            'expiration_date'                   =>  $request->expiration_date,
            'best_before_date'                  =>  $request->best_before_date,
            'production_batch_number'           =>  $request->production_batch_number,
            'relevance_code'                    =>  $spec->relevance_code,
            'need_num'                          =>  $request->need_num,
            'location_code'                     =>  $request->location_code,
            'purchase_price'                    =>  $request->purchase_price,
            'location_id'                       =>  $location->id??0,
            'remark'                            =>  $request->remark,
        ];

        return formatRet(0, '', $newResult);
    }

    /**
     * 导入库存
     **/
    public function import(CreateBatchRequest $request) {
        app('log')->info('导入库存的单个信息', $request->all());

        app('log')->info('新增入库单', $request->all());
        app('db')->beginTransaction();
        try{
            $data = $request->all();
            $data["warehouse_id"] = app('auth')->warehouse()->id;;
            $batch = app('batch')->create($data);
            $batch->load("batchProducts");
            $insertData = $batch->toArray();
            $data = $insertData["batch_products"];
            
            $stockItems = [];
            foreach ($data as $key => $value) {
                $stockItems[] = [
                    "stock_id"              =>  $value["id"],
                    "stockin_num"           =>  $value["need_num"],
                    "box_code"              =>  $value["box_code"],
                    "distributor_code"      =>  $value["distributor_code"],
                    "ean"                   =>  $value["ean"],
                    "expiration_date"       =>  $value["expiration_date"],
                    "best_before_date"      =>  $value["best_before_date"],
                    "production_batch_number"           =>  $value["production_batch_number"],
                    "remark"                =>  $value["remark"],
                    "code"                  =>  $value["location_code"],
                ];
            }
            $res = app('store')->InAndPutOn(app('auth')->warehouse()->id,$stockItems,$batch->id, 1);

            app('db')->commit();
            return formatRet(0);
        } catch(LocationException $e) {
            app('db')->rollback();
            app('log')->error('货位不存在',['msg' =>$e->getMessage()]);
            return formatRet(404, $e->getMessage(), $e->getLocations());
        } catch (\Exception $e){

            app('db')->rollback();
            app('log')->error('新增入库上架失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans('message.batchAddFailed'));
        }

    }

}
