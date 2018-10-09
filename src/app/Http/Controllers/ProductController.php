<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductStock;
use App\Models\WarehouseEmployee;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->warehouse = app('auth')->warehouse();
    }

    /**
     * 货品 - 列表
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page' => 'integer|min:1',
            'page_size' => new PageSize,
            'category_id' => 'integer|min:1',
            'updated_at_b' => 'date:Y-m-d',
            'updated_at_e' => 'date:Y-m-d',
            'keywords' => 'string',
        ]);

	$product = Product::with(['category', 'specs', 'owner:id,email'])
            ->ofWarehouse($this->warehouse->id)
            ->latest('updated_at');

    	if (app('auth')->roleId() == WarehouseEmployee::ROLE_RENTER) {
    		$product->whose(app('auth')->id());
    	}

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

        $products = $product->paginate($request->input('page_size'));

        return formatRet(0, '', $products->toArray());
    }

    /**
     * 货品 - 详细
     */
    public function show($product_id)
    {
        $product = Product::with('specs')->ofWarehouse($this->warehouse->id)->findOrFail($product_id);

        return formatRet(0, '', $product->toArray());
    }

    /**
     * 货品 - 新增
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'category_id'               => 'required|integer|min:1',
            'name_cn'                   => 'required|string|max:255',
            'name_en'                   => 'required|string|max:255',
            'hs_code'                   => 'string|max:255',//海关编码
            'origin'                    => 'string|max:20',//产地
            'display_link'              => 'url',//展示链接
            'remark'                    => 'string|max:255',
            'photos'                    => 'string|max:255',
            'specs'                     => 'required|array',
            'specs.*.name_cn'           => 'required|string|max:255',
            'specs.*.name_en'           => 'required|string|max:255',
            'specs.*.net_weight'        => 'present|numeric',
            'specs.*.gross_weight'      => 'present|numeric',
            'specs.*.relevance_code'    => 'required|string|max:255|distinct',
        ]);

        $category = Category::ofWarehouse(app('auth')->warehouse()->id)->find($request->category_id);
        if (empty($category)) {
            return formatRet(404, '分类不存在');
        }

        if ($category->is_enabled == 0) {
            return formatRet(500, '分类被禁用');
        }

        $specs = [];
        foreach ($request->specs as $spec) {
            $exists = ProductSpec::whose(Auth::id())->where('relevance_code', $spec['relevance_code'])->first();
            if ($exists) {
                return formatRet(500, '外部编码' . $spec['relevance_code'] . '已被使用');
            }

            $specs[] = [
                'product_id'     => 0,
                'name_cn'        => $spec['name_cn'],
                'name_en'        => $spec['name_en'],
                'net_weight'     => $spec['net_weight'],
                'gross_weight'   => $spec['gross_weight'],
                'relevance_code' => $spec['relevance_code'],
                'owner_id'       => Auth::id(),
		        'warehouse_id'   => app('auth')->warehouse()->id,
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
        $product->owner_id            = Auth::id();
	    $product->warehouse_id	      = app('auth')->warehouse()->id;    	

        if ($product->save() && $specs) {
            foreach ($specs as $k => $v) {
                $specs[$k]['product_id'] = $product->id;
            }
            ProductSpec::insert($specs);
        }

        return formatRet(0);
    }

    /**
     * 货品 - 编辑
     *
     * @author liusen
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'product_id'          => 'required|integer|digits_between:1,11',
            'category_id'         => 'required|integer|digits_between:1,11',
            'name_cn'             => 'required|string|max:255',
            'name_en'             => 'required|string|max:255',
            'hs_code'             => 'present|string|max:255',
            'origin'              => 'present|string|max:20',
            'display_link'        => 'present|url',
            'remark'              => 'present|string|max:255',
            'photos'              => 'present|string|max:255',
        ]);

        if (! $category = Category::ofWarehouse($this->warehouse->id)->find($request->category_id)) {
            return formatRet(404, '分类不存在', [], 404);
        }

        if (! $product = Product::ofWarehouse($this->warehouse->id)->find($request->product_id)) {
            return formatRet(404, '货品不存在', [], 404);
        }

        $product->category_id         = $request->category_id;
        $product->name_cn             = $request->name_cn;
        $product->name_en             = $request->name_en;
        $product->hs_code             = $request->hs_code;
        $product->origin              = $request->origin;
        $product->display_link        = $request->display_link;
        $product->remark              = $request->remark;
        $product->photos              = $request->photos;

        if ($product->save()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }

    /**
     * 货品 -  批量修改分类
     */
    public function setCategory(Request $request)
    {
       $this->validate($request, [
	       'product_ids' => 'required|array',
	       'product_ids.*.id'=> 'required|exists:product,id',
	       'category_id'=> 'required|exists:category,id',
       ]);
       $data = [];
       app('db')->beginTransaction();
       foreach($request->product_ids as $key=>$val){
	       if(!$product = Product::whose(Auth::id())->find($val['id'])){
		       return formatRet(500, '不能操作租赁用户产品', []); 
	       }
	       try {
		       $data[$key] =  Product::where('id',$val['id'])->update(['category_id'=>$request->category_id]);
	       }catch (\Exception $e) { 
		       app('db')->rollback();
		       return formatRet(500, '批量修改分类失败');

	       }
       }
       app('db')->commit();
       return formatRet(0, '批量修改分类成功');

    }
}
