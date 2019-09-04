<?php

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
use App\Services\Service\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Validators\ValidationException;
use Validator;


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
            ->ofWarehouse($request->warehouse_id)
            ->where('product.owner_id',app('auth')->ownerId())
            ->select(['product.id','product.name_cn','product.name_en','origin','photos','purchase_price','sale_price','total_floor_num','total_lock_num','total_shelf_num','total_stockin_num','total_stockout_num','category_id', 'product.updated_at', 'product.warehouse_id','total_stock_num', 'category.name_cn as category_name_cn', 'category.name_en as category_name_en'])
            ->latest('updated_at');
        if($request->filled('category_id')){
            $product = $product->where('category_id',$request->category_id);
        }

        if ($request->filled('updated_at_b')) {
            $product = $product->where('updated_at', '>', strtotime($request->updated_at_b));
        }

        if ($request->filled('updated_at_e')) {
            $product = $product->where('updated_at', '<', strtotime($request->updated_at_e));
        }

        if ($request->filled('keywords')) {
            $product = $product->hasKeyword($request->keywords);
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
    public function store( CreateProductRequest $request)
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
                'warehouse_id'   => $request->warehouse_id,
                'is_warning'     => 1
            ];
            $exists = ProductSpec::whose(app('auth')->ownerId())->where('relevance_code', $spec['relevance_code'])->first();
            if ($exists) {
                return formatRet(500, trans('message.relevanceCodeIsUsed',['relevance_code'=>$spec['relevance_code']]));
            }
        }



        $product = new Product;
        $product->category_id         = $request->category_id;
        $product->name_cn             = $request->name_cn;
        $product->name_en             = $request->input('name_en', $request->name_cn);
        $product->hs_code             = $request->hs_code;
        $product->origin              = $request->origin;
        $product->display_link        = $request->input('display_link');
        $product->remark              = $request->input('remark');
        $product->photos              = $request->input('photos');
        $product->owner_id            = Auth::ownerId();
        $product->warehouse_id	      = $request->warehouse_id;
        $product->sale_price          = $specs[0]['sale_price'];
        $product->purchase_price      = $specs[0]['purchase_price'];
        DB::beginTransaction();
  
        try{
            $product->save();
            foreach ($specs as $k => $v) {
                $specs[$k]['product_id']    = $product->id;
            }
            ProductSpec::insert($specs);
            DB::commit();
            return formatRet(0);
        }catch (\Exception $e){
            DB::rollBack();
            app('log')->error('新增货品失败',['msg'=>$e->getMessage()]);
            return formatRet(500,"新增失败");
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
        $product->load("specs");
        $product->category_id         = $request->category_id;
        $product->name_cn             = $request->name_cn;
        $product->name_en             = $request->input('name_en', $request->name_cn);
        $product->remark              = $request->remark;
        $product->photos              = $request->photos;
        $product->sale_price          = $request->specs[0]['sale_price'];
        $product->purchase_price      = $request->specs[0]['purchase_price'];
        DB::beginTransaction();
        try{
            $product->save();
            $existIdArr = $product->specs->pluck("id")->toArray();
            $updateIdArr = collect($request->specs)->pluck("id")->toArray();
            $willRemoveIdArr = array_diff($existIdArr, $updateIdArr);
            $existIdArr[] = 0;
            foreach ($request->specs as $spec) {

                if(!in_array($spec["id"], $existIdArr)) {
                    throw new \Exception("不存在的规格ID", 1);
                }

                $exists = ProductSpec::whose(Auth::ownerId())->where('relevance_code', $spec['relevance_code'])->where('id','!=', $spec['id'])->first();
                if ($exists) {
                    return formatRet(500, trans('message.relevanceCodeIsUsed',['relevance_code'=>$spec['relevance_code']]));
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
                    'warehouse_id'   => $request->warehouse_id,
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
                    return formatRet(500, "不允许删除此规格,规格下面的库存");
                } else {
                    //将多余的删除掉
                    ProductSpec::where('id', $id)->where('owner_id', Auth::ownerId())->delete();
                    ShopProductSpec::where('spec_id', $id)->delete();
                }
            }


            //同步商品库分类ID
            ShopProduct::where('product_id', $product->id)->update(['category_id'=> $product->category_id]);

            DB::commit();
            return formatRet(0,'编辑商品成功');
        }catch(\Exception $e) {
            DB::rollBack();
            app('log')->error('编辑商品失败',['msg' => $e->getMessage()]);
            return formatRet(500, '编辑商品失败');
        }
    }

    public  function destroy($product_id)
    {
        app('log')->error('删除货品', ["product_id" => $product_id]);

        $product = Product::where('owner_id', app('auth')->ownerId())->find($product_id);

        if(!$product){
            return formatRet(500,"货品不存在");
        }

        $spec = ProductSpec::where('product_id',$product->id)->has('stocks')->get();
        if(count($spec) >0 ){
            return formatRet(500,"不允许删除此商品");
        }

        DB::beginTransaction();
        try{
            ShopProduct::where('product_id', $product_id)->delete();
            ShopProductSpec::where('product_id', $product_id)->delete();
            // $product->specs()->stocks()->delete();
            $product->specs()->delete();
            $product->delete();
            DB::commit();
            return formatRet(0);
        }catch (\Exception $e){
            DB::rollBack();
            app('log')->error('删除货品失败', ["msg" => $e->getMessage()]);
            return formatRet(500,"删除货品失败");
        }
    }



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
           
            $result = $resultAll[0];
            // app('log')->info('result', $result);
            
            foreach ($result as $key => $row) {
                // print_r($row);

                $category  = Category::where('name_cn', $row['category_name'])
                ->where('warehouse_id',app('auth')->warehouse()->id)
                ->where('owner_id',app('auth')->ownerId())->first();
                if(!$category) {
                    return formatRet(0, "{$row['category_name']}分类不存在");
                }

                $product = [
                    'warehouse_id'  =>  app('auth')->warehouse()->id,
                    'name_cn'       =>  $row['name_cn'],
                    'name_en'       =>  $row['name_cn'],
                    'remark'        =>  $row['remark'],
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
                foreach ($specs as $kk=> $spec) {

                    if(empty($spec[0]) && empty($spec[1])) {
                        continue;
                    }



                    $specRow = [
                        'name_cn'           =>  $spec[0],
                        'name_en'           =>  $spec[0],
                        'net_weight'        =>  trim($spec[4]),
                        'gross_weight'      =>  trim($spec[4]),
                        'relevance_code'    =>  trim($spec[1]),
                        'purchase_price'    =>  trim($spec[2]),
                        'sale_price'        =>  trim($spec[3]),
                        'product_id'        =>  0,
                        'owner_id'          =>  app('auth')->ownerId(),
                        'warehouse_id'      =>  app('auth')->warehouse()->id,
                    ];

                    $product['specs'][] =  $specRow;
                }

                
                $newResult[] =  $product;
            }

            // app('log')->info('商品信息xxxxx', $result);

        } catch (ValidationException $e) {
            $failures = $e->failures();
            $error = [];
            foreach ($failures as $failure) {
                $error[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'error' => $failure->errors()
                ];
            }
            return formatRet(0, '导入结束,数据验证未通过', $error);
        } catch(\Exception $exception) {
            
            app('log')->error('货品导入失败', ["msg" => $exception->getMessage()]);
            return formatRet(500, '导入失败');
        }


        $productRequest = new CreateProductRequest;


        DB::beginTransaction();
        try{
           
            $products = [];
            foreach ($newResult as $key => $product) {
                
                $validator = Validator::make($product, $productRequest->rules());   
                if ($validator->fails()) 
                {
                    throw new \Exception($validator->errors()->first(), 1);
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

    public  function  show(BaseRequests $request,$product_id)
    {
        app('log')->error('查看详情', ["product_id" => $product_id]);
        $this->validate($request,[
            'warehouse_id' => [
                'required','min:1',
                Rule::exists('warehouse','id')->where('owner_id',app('auth')->ownerId())
            ]
        ]);
        $product = Product::with(['category:id,name_cn', 'specs:id,name_cn,name_en,net_weight,gross_weight,relevance_code,product_id,is_warning,sale_price,purchase_price'])
            ->ofWarehouse($request->warehouse_id)
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

}
