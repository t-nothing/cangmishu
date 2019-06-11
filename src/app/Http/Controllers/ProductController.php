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
        $product = Product::with(['category:id,name_cn', 'specs:id,name_cn,name_en,net_weight,gross_weight,relevance_code,product_id'])
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
                'name_en'        => $spec['name_en'],
                'net_weight'     => $spec['net_weight'],
                'gross_weight'   => $spec['gross_weight'],
                'relevance_code' => $spec['relevance_code'],
                'owner_id'       => Auth::ownerId(),
                'warehouse_id'   => $request->warehouse_id,
            ];
        }

        $product = new Product;
        $product->category_id         = $request->category_id;
        $product->name_cn             = $request->name_cn;
        $product->name_en             = $request->name_en;
        $product->hs_code             = $request->hs_code;
        $product->origin              = $request->origin;
        $product->display_link        = $request->input('display_link');
        $product->remark              = $request->input('remark');
        $product->photos              = $request->input('photos');
        $product->owner_id            = Auth::ownerId();
        $product->warehouse_id	      = $request->warehouse_id;

        try{
            $product->save();
            foreach ($specs as $k => $v) {
                $specs[$k]['product_id'] = $product->id;
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
        $product->category_id         = $request->category_id;
        $product->name_cn             = $request->name_cn;
        $product->name_en             = $request->name_en;
        $product->remark              = $request->remark;
        $product->photos              = $request->photos;
        DB::beginTransaction();
       try{
           $product->save();
           foreach ($request->specs as $spec) {
               $data= [
                   'name_cn'        => $spec['name_cn'],
                   'name_en'        => $spec['name_en'],
                   'net_weight'     => $spec['net_weight'],
                   'gross_weight'   => $spec['gross_weight'],
                   'is_warning'     => $spec['is_warning']
               ];
                ProductSpec::updateOrCreate(['relevance_code'=>$spec['relevance_code']],$data);
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
        DB::beginTransaction();
        try{
            $product->delete();
            $product->specs()->delete();
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
        $product = Product::with(['category:id,name_cn', 'specs:id,name_cn,name_en,net_weight,gross_weight,relevance_code,product_id'])
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
