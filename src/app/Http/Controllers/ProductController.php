<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Services\Service\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Validators\ValidationException;

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
        $product = Product::with(['category:id,name_cn', 'specs:id,name_cn,name_en,net_weight,gross_weight,relevance_code,product_id,purchase_price,sale_price'])
            ->ofWarehouse($request->warehouse_id)
            ->where('owner_id',app('auth')->ownerId())
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

        $products = $product->paginate($request->input('page_size',10));

        return formatRet(0, '', $products->toArray());
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

        try{
            $product->save();
            foreach ($specs as $k => $v) {
                $specs[$k]['product_id']    = $product->id;
            }
            ProductSpec::insert($specs);
            return formatRet(0);
        }catch (\Exception $e){
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

                $data= [
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
                    ProductSpec::where('id', $willRemoveIdArr)->where('owner_id', Auth::ownerId())->delete();
                }
            }

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
        try {
            $productImport = new ProductsImport(new CategoryService);
            app('excel')->import($productImport, $request->file('file'));
            return formatRet(0, '导入成功');
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
