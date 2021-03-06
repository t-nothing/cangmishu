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
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\Category;
use App\Models\ShopProduct;
use App\Models\ShopProductSpec;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexProductRequest $request)
    {
        app('log')->info('查询列表',$request->all());
        $product = Product::leftjoin('category', 'category.id','=', 'product.category_id')
        ->with(['specs:id,name_cn,name_en,net_weight,gross_weight,relevance_code,product_id,purchase_price,sale_price,total_stock_num'])
            ->ofWarehouse(app('auth')->warehouse()->id)
            ->where('product.owner_id',app('auth')->ownerId())
            ->select(['product.id','product.name_cn','product.name_en','product.barcode','origin','photos','purchase_price','sale_price','total_floor_num','total_lock_num','total_shelf_num','total_stockin_num','total_stockout_num','category_id', 'product.updated_at', 'product.warehouse_id','total_stock_num', 'category.name_cn as category_name_cn', 'category.name_en as category_name_en'])
            ->latest('updated_at');
        if($request->filled('category_id')){
            $product = $product->where('category_id',$request->category_id);
        }

        if ($request->filled('created_at_b')) {
            $product = $product->where('product.created_at', '>', strtotime($request->created_at_b));
        }

        if ($request->filled('created_at_e')) {
            $product = $product->where('product.created_at', '<', strtotime($request->created_at_e));
        }

        if ($request->filled('keywords')) {

            if($request->keywords == "库存不足") {
                $request->merge(['show_low_stock'   =>  1]);
            } else {
                $product = $product->hasKeyword($request->keywords);
            }

        }

        if ($request->filled('show_low_stock') && $request->show_low_stock == 1) {
            $product = $product->whereRaw('product.total_stock_num <= category.warning_stock and category.warning_stock >0');
        }

        $products = $product->paginate($request->input('page_size',10));

        $result = $products->toArray();
        foreach ($result['data'] as $key => $value) {
            $result['data'][$key]['category'] = [
                'name_cn'   => $value['category_name_cn'],
                'name_en'   => $value['category_name_en'],
            ];
            unset($value['category_name_cn']);
            unset($value['category_name_en']);
        }

        return formatRet(0, '', $result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateProductRequest $request)
    {
        app('log')->info('新增商品',$request->all());
        $specs = [];
        foreach ($request->specs as $spec) {
            $specs[] = [
                'product_id'     => 0,
                'name_cn'        => $spec['name_cn'],
                'name_en'        => $spec['name_en']??$spec['name_cn'],
                'net_weight'     => $spec['net_weight']??$spec['gross_weight'],
                'gross_weight'   => $spec['gross_weight'],
                'relevance_code' => $spec['relevance_code'],
                'sale_price'     => $spec['sale_price'],
                'purchase_price' => $spec['purchase_price'],
                'owner_id'       => Auth::ownerId(),
                'warehouse_id'   => app('auth')->warehouse()->id,
                'is_warning'     => 1
            ];

            $exists = ProductSpec::whose(app('auth')->ownerId())
                ->where('warehouse_id', \auth('admin')->getWarehouseIdForRequest())
                ->where('relevance_code', $spec['relevance_code'])
                ->first();

            if ($exists) {
                return formatRet(500, trans('message.productRelevanceCodeIsUsed',['relevance_code'=>$spec['relevance_code']]));
            }
        }

        $barcode =  $request->barcode??"";
        if($barcode != "") {
            $existsCount = Product::where("barcode", $barcode)->where("warehouse_id", app('auth')->warehouse()->id)->count();
            if($existsCount > 0) {
                return formatRet(500, "条码已存在,请重新更换");
            }
        }

        $product = new Product;
        if($request->category_id > 0){
            $product->category_id         = $request->category_id;
        }

        $product->name_cn             = $request->name_cn;
        $product->name_en             = $request->input('name_en', $request->name_cn);
        $product->hs_code             = $request->hs_code;
        $product->origin              = $request->origin;
        $product->display_link        = $request->input('display_link');
        $product->remark              = $request->input('remark', '');
        $product->photos              = $request->input('photos');
        $product->owner_id            = Auth::ownerId();
        $product->warehouse_id	      = app('auth')->warehouse()->id;
        $product->sale_price          = $specs[0]['sale_price'];
        $product->purchase_price      = $specs[0]['purchase_price'];
        $product->barcode             = $barcode;
        DB::beginTransaction();

        try{
            $product->save();
            foreach ($specs as $k => $v) {
                $specs[$k]['product_id']    = $product->id;
            }
            ProductSpec::insert($specs);
            DB::commit();

            $product = Product::with(['category:id,name_cn', 'specs:id,name_cn,name_en,net_weight,gross_weight,relevance_code,product_id,is_warning,sale_price,purchase_price,total_stock_num'])
            ->ofWarehouse(app('auth')->warehouse()->id)
            ->where('owner_id', app('auth')->ownerId())
            ->where('id', $product->id)
            ->first();

            return formatRet(0, '', $product->toArray());
        }catch (\Exception $e){
            DB::rollBack();
            app('log')->error('新增货品失败',['msg'=>$e->getMessage()]);
            return formatRet(500, trans("message.productAddFailed"));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductRequest $request, $product_id)
    {
        app('log')->info('编辑商品',$request->all());
        $product = Product::find($product_id);
        $barcode =  $request->barcode??"";
        $product->load("specs");
        $product->category_id         = $request->category_id;
        $product->name_cn             = $request->name_cn;
        $product->name_en             = $request->input('name_en', $request->name_cn);
        $product->remark              = $request->remark;
        $product->photos              = $request->photos;
        $product->sale_price          = $request->specs[0]['sale_price'];
        $product->purchase_price      = $request->specs[0]['purchase_price'];
        if($product->barcode != $barcode) {
            $existsCount = Product::where("barcode", $barcode)
                ->where("warehouse_id", app('auth')->warehouse()->id)
                ->count();

            if($existsCount > 0) {
                return formatRet(500, "条码已存在,请重新更换");
            }
        }
        $product->barcode             = $request->barcode??"";
        DB::beginTransaction();
        try{
            $product->save();
            $existIdArr = $product->specs->pluck("id")->toArray();
            $updateIdArr = collect($request->specs)->pluck("id")->toArray();
            $willRemoveIdArr = array_diff($existIdArr, $updateIdArr);
            $existIdArr[] = 0;
            foreach ($request->specs as $spec) {

                if(!in_array($spec["id"], $existIdArr)) {
                    throw new \Exception(trans("message.productSpecNotExists"), 1);
                }

                $exists = ProductSpec::whose(Auth::ownerId())
                    ->where('relevance_code', $spec['relevance_code'])
                    ->where('warehouse_id', \auth('admin')->getWarehouseIdForRequest())
                    ->where('id','!=', $spec['id'])
                    ->first();

                if ($exists) {
                    return formatRet(500, trans('message.productRelevanceCodeIsUsed',['relevance_code'=>$spec['relevance_code']]));
                }

                $data = [
                    'id'             => $spec['id'],
                    'name_cn'        => $spec['name_cn'],
                    'name_en'        => $spec['name_en']??$spec['name_cn'],
                    'net_weight'     => $spec['net_weight']??$spec['gross_weight'],
                    'gross_weight'   => $spec['gross_weight'],
                    'relevance_code' => $spec['relevance_code'],
                    'sale_price'     => $spec['sale_price'],
                    'purchase_price' => $spec['purchase_price'],
                    'owner_id'       => Auth::ownerId(),
                    'warehouse_id'   => app('auth')->warehouse()->id,
                    'is_warning'     => 1,
                    'product_id'     => $product->id,
                ];

                ProductSpec::updateOrCreate(
                    [
                        'relevance_code'=>$spec['relevance_code'],
                        'owner_id'      =>Auth::ownerId()
                    ],
                    $data
                );
            }

            foreach ($willRemoveIdArr as $key => $id) {
                $spec = ProductSpec::where('id',$id)->has('stocks')->get();
                if(count($spec) >0 ){
                    return formatRet(500, trans("message.productSpecCannotDelete",["spec_name"=>$spec['name_cn']]));
                } else {
                    //将多余的删除掉
                    ProductSpec::where('id', $id)->where('owner_id', Auth::ownerId())->delete();
                    ShopProductSpec::where('spec_id', $id)->delete();
                }
            }


            //同步商品库分类ID
            ShopProduct::where('product_id', $product->id)->update(['category_id'=> $product->category_id]);

            DB::commit();
            return formatRet(0, trans("message.success"));
        }catch(\Exception $e) {
            DB::rollBack();
            app('log')->error('编辑商品失败',['msg' => $e->getMessage()]);
            return formatRet(500, trans("message.productUpdateFailed"));
        }
    }

    public  function destroy($product_id)
    {
        app('log')->error('删除货品', ["product_id" => $product_id]);

        $product = Product::where('owner_id', app('auth')->ownerId())->find($product_id);

        if(!$product){
            return formatRet(500, trans("message.productNotExist"));
        }

        $spec = ProductSpec::where('product_id',$product->id)->has('stocks')->get();
        if(count($spec) >0 ){
            return formatRet(500, trans("message.productCannotDelete"));
        }

        DB::beginTransaction();
        try{
            ShopProduct::where('product_id', $product_id)->delete();
            ShopProductSpec::where('product_id', $product_id)->delete();
            // $product->specs()->stocks()->delete();

            $product->specs()->forceDelete();
            $product->forceDelete();
            DB::commit();
            return formatRet(0);
        }catch (\Exception $e){
            DB::rollBack();
            app('log')->error('删除货品失败', ["msg" => $e->getMessage()]);
            return formatRet(500, trans("message.productDeleteFailed"));
        }
    }

    /**
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws ValidationException
     */
    public function import(BaseRequests $request)
    {
        $this->validate($request,[
            'file' =>'required|file',
            'warehouse_id' => [
                'required','min:1',
                Rule::exists('warehouse','id')->where('owner_id',app('auth')->ownerId())
            ]
        ]);

        $newResult = [];
        try {
            $productImport = new ProductsImport(new CategoryService);
            $resultAll = app('excel')->toArray($productImport, $request->file('file'), 'UTF-8');

            $result = collect($resultAll[0])->filter(function ($value) {
                return $value['name_cn'] && $value['category_name'];
            })->all();

            foreach ($result as $key => $row) {
                validator(
                    $row,
                    [
                        'name_cn' => 'required',
                        'category_name' => 'required',
                    ],
                    [],
                    [
                        'name_cn' => '商品名称',
                        'category_name' => '商品分类',
                    ]
                )->validate();

                $category = Category::where('name_cn', $row['category_name'])
                    ->where('warehouse_id', app('auth')->warehouse()->id)
                    ->where('owner_id', app('auth')->ownerId())
                    ->first();

                if(! $category) {
                    return formatRet(422, "{$row['category_name']}分类不存在");
                }

                $product = [
                    'warehouse_id'  =>  app('auth')->warehouse()->id,
                    'name_cn'       =>  trim($row['name_cn']),
                    'name_en'       =>  trim($row['name_cn']),
                    'remark'        =>  trim($row['remark']),
                    'category_id'   =>  $category->id,
                    'owner_id'      =>  app('auth')->ownerId(),
                ];

                // app('log')->info('product', $product);
                unset($row['name_cn']);
                unset($row['remark']);
                unset($row['category_name']);


                if(count($row) % 5 !== 0) {

                    app('log')->info('规格内容长度', $row);
                    throw new \Exception("导入模板错误", 1);
                }
                $specs = array_chunk($row, 5);
                $product_purchase_price = 0;
                $product_sale_price = 0;
                foreach ($specs as $kk=> $spec) {
                    if(empty($spec[0]) && empty($spec[1])) {
                        continue;
                    }

                    $specRow = [
                        'name_cn'           =>  trim($spec[0]),
                        'name_en'           =>  trim($spec[0]),
                        'net_weight'        =>  trim($spec[4]),
                        'gross_weight'      =>  trim($spec[4]),
                        'relevance_code'    =>  trim($spec[1]),
                        'purchase_price'    =>  trim($spec[2]),
                        'sale_price'        =>  trim($spec[3]),
                        'product_id'        =>  0,
                        'owner_id'          =>  app('auth')->ownerId(),
                        'warehouse_id'      =>  app('auth')->warehouse()->id,
                    ];

                    validator(
                        $specRow,
                        [
                            'name_cn' => 'required',
                            'relevance_code' => 'required|regex:/^[a-zA-Z0-9_]{3,}$/',
                        ],
                        [
                            'regex' => 'SKU编码只能是字母数字下划线，长度大于3位',
                        ],
                        [
                            'name_cn' => '规格名称',
                            'relevance_code' => '规格SKU',
                        ]
                    )->validate();

                    $product_purchase_price = trim($spec[2]);
                    $product_sale_price = trim($spec[3]);

                    $product['specs'][] =  $specRow;
                }

                $product['purchase_price']  = $product_purchase_price;
                $product['sale_price']      = $product_sale_price;


                $newResult[] =  $product;
            }

            // app('log')->info('商品信息xxxxx', $result);

        } catch (ValidationException $e) {
            /*$failures = $e->failures();
            $error = [];
            foreach ($failures as $failure) {
                $error[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'error' => $failure->errors()
                ];
            }*/
            $errorMessage = array_values($e->errors())[0][0];
            return formatRet(422, '导入结束,数据验证未通过：'. $errorMessage);
        } catch(\Exception $exception) {

            app('log')->error('货品导入失败', ["msg" => $exception->getMessage()]);
            return formatRet(500, '导入失败');
        }


        $productRequest = new CreateProductRequest;


        DB::beginTransaction();
        try{

            $products = [];
            foreach ($newResult as $key => $product) {

                $validator = Validator::make($product, $productRequest->rules(), [], $productRequest->attributes());
                if ($validator->fails())
                {
                    throw new \Exception("第".($key+1)."行" . $validator->errors()->first(), 1);
                }

                $specs = $product['specs'];
                unset($product['specs']);
                $productModel = new Product($product);
                $productModel->save();
                $productModel->specs()->createMany($specs);
            }

            DB::commit();
            return formatRet(0);
        }catch (\Exception $e){
            DB::rollBack();
            app('log')->error('导入货品失败', ["msg" => $e->getMessage()]);
            return formatRet(500,"导入货品失败:".$e->getMessage());
        }
    }

    /**
     * 商品详细
     **/
    public  function  show(BaseRequests $request,$product_id)
    {
        app('log')->error('查看详情', ["product_id" => $product_id]);

        $product = Product::with(['category:id,name_cn', 'specs:id,name_cn,name_en,net_weight,gross_weight,relevance_code,product_id,is_warning,sale_price,purchase_price,total_stock_num'])
            ->ofWarehouse(app('auth')->warehouse()->id)
            ->where('owner_id', app('auth')->ownerId())
            ->where('id', $product_id)
            ->first();
        if(!$product){
            $product= [];
        }else{
            $product= $product->toArray();
        }
        return formatRet(0, "成功", $product);

    }

    /**
     * 汇总统计
     */
    public function total() {
        $totalCount = Product::ofWarehouse(app('auth')->warehouse()->id)
            ->where('owner_id', app('auth')->ownerId())
            ->count();

        $totalStockNum = Product::ofWarehouse(app('auth')->warehouse()->id)
            ->where('owner_id', app('auth')->ownerId())
            ->sum("total_stock_num");

        return formatRet(0, "成功", [
            "count_product" =>  intval($totalCount),
            "count_stock"   =>  intval($totalStockNum),
        ]);
    }

    /**
     * 商品详细扫码
     **/
    public  function  scan(BaseRequests $request)
    {
        $this->validate($request, [
            'barcode' => 'required|string|max:255'
        ]);

        $productId = 0;
        //先查一下商品条码
        $productInfo = Product::where("barcode", $request->barcode)
            ->ofWarehouse(app('auth')->warehouse()->id)
            ->where('owner_id', app('auth')->ownerId())->first();
        if(!$productInfo) {
            $specInfo = ProductSpec::where("relevance_code", $request->barcode)
            ->ofWarehouse(app('auth')->warehouse()->id)
            ->where('owner_id', app('auth')->ownerId())->first();
            if(!$specInfo) {
                return formatRet(500, trans("message.productNotExist"));
            }

            $productId = $specInfo->product_id;
        } else {
            $productId = $productInfo->id;
        }

        $product = Product::with(['category:id,name_cn', 'specs:id,name_cn,name_en,net_weight,gross_weight,relevance_code,product_id,is_warning,sale_price,purchase_price,total_stock_num'])
            ->ofWarehouse(app('auth')->warehouse()->id)
            ->where('owner_id', app('auth')->ownerId())
            ->where('id', $productId)
            ->first();
        if(!$product){
            $product= [];
        }else{
            $product= $product->toArray();
        }
        return formatRet(0, "成功", $product);

    }
}
