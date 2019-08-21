<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\IndexProductRequest;
use App\Imports\ProductSpecImport;
use App\Services\Service\ProductService;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSpec;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Validators\ValidationException;

class ProductSpecController extends Controller
{

    /**
     * 货品规格列表
     */
    public function index(IndexProductRequest $request)
    {
        // $product = Product::ofWarehouse($this->warehouse->id)->find($product_id);

        // if(app('auth')->isLimited()){
        //     $product->whereIn('owner_id',app('auth')->ownerId());
        // }

        // if (! $product) {
        //     return formatRet(404, '货品不存在', [], 404);
        // }

        // return formatRet(0, '', $product->specs->toArray());
        $product = ProductSpec::leftjoin('product', 'product.id','=', 'product_spec.product_id')
            ->where('product_spec.warehouse_id',$request->warehouse_id)
            ->where('product_spec.owner_id',app('auth')->ownerId())
            ->select('product_spec.*')
            ->latest('product_spec.updated_at');
        if($request->filled('category_id')){
            $product = $product->where('product.category_id',$request->category_id);
        }

        if ($request->filled('updated_at_b')) {
            $product = $product->where('product_spec.updated_at', '>', strtotime($request->updated_at_b));
        }

        if ($request->filled('updated_at_e')) {
            $product = $product->where('product_spec.updated_at', '<', strtotime($request->updated_at_e));
        }

        if ($request->filled('keywords')) {
            $product = $product->hasKeyword($request->keywords);
        }

        $paginator = $product->paginate($request->input('page_size',10));
        $data = $paginator->makeHidden(['product','deleted_at', 'created_at', 'updated_at', 'is_warning', 'name_cn', 'name_en']);

        $model = new ProductSpec;
        foreach ($data as $k => $v) {
            $model->product = $v['product'];
            $model->name_cn = $v['name_cn'];
            $model->name_en = $v['name_en'];
            $data[$k]['product_name'] = $model->product_name;
        }
        $paginator->data = $data;


        return formatRet(0, '', $paginator->toArray());
    }

    /**
     * 货品规格 - 新增
     */
    public function store(BaseRequests $request)
    {
        $this->validate($request, [
            'product_id'     => 'required|integer|min:1',
            'name_cn'        => 'required|string|max:255',
            'name_en'        => 'required|string|max:255',
            'net_weight'     => 'present|numeric',
            'gross_weight'   => 'present|numeric',
            'relevance_code' => 'required|string|max:255',
        ]);

        $spec = new ProductSpec;

        $product = Product::whose(Auth::ownerId())->find($request->product_id);
        if (empty($product)) {
            return formatRet(404, '货品不存在', [], 404);
        }

        $this->validate($request, [
            'relevance_code' => Rule::unique($spec->getTable())->where(function ($query) use ($request) {
                return $query->whereIn('owner_id', app('auth')->ownerId())
                    ->where('relevance_code', $request->relevance_code);
            }),
        ]);

        $spec->warehouse_id   = app('auth')->warehouse()->id;
        $spec->product_id     = $request->product_id;
        $spec->name_cn        = $request->name_cn;
        $spec->name_en        = $request->name_en;
        $spec->net_weight     = $request->net_weight;
        $spec->gross_weight   = $request->gross_weight;
        $spec->relevance_code = $request->relevance_code;
        $spec->owner_id       = app('auth')->ownerId();
        $spec->save();

        return formatRet(0);
    }

    /**
     * 货品规格 - 修改
     *
     * @author liusen
     */
    public function edit(BaseRequests $request)
    {
        $this->validate($request, [
            'spec_id'        => 'required|integer|min:1',
            'name_cn'        => 'required|string|max:255',
            'name_en'        => 'required|string|max:255',
            'net_weight'     => 'present|numeric',
            'gross_weight'   => 'present|numeric',
            'relevance_code' => 'required|string|max:255',
        ]);

        if (! $spec = ProductSpec::whereIn('owner_id', app('auth')->ownerId())->find($request->spec_id)) {
            return formatRet(404, '商品规格不存在', [], 404);
        }

        $this->validate($request, [
            'relevance_code' => Rule::unique($spec->getTable())->ignore($spec->id)->where(function ($query) use ($request) {
                return $query->where('owner_id', Auth::ownerId())
                    ->where('relevance_code', $request->relevance_code);
            }),
        ]);

        $spec->name_cn        = $request->name_cn;
        $spec->name_en        = $request->name_en;
        $spec->net_weight     = $request->net_weight;
        $spec->gross_weight   = $request->gross_weight;
        $spec->relevance_code = $request->relevance_code;

        if ($spec->save()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }

    /**
     * 货品规格 - 删除
     *
     * @author liusen
     */
    public function destroy($spec_id)
    {
        // if (! $spec = ProductSpec::where('owner_id', Auth::ownerId())->find($spec_id)) {
        //     return formatRet(404, '商品规格不存在', [], 404);
        // }

        // if ($spec->delete()) {
        //     return formatRet(0);
        // }

        // return formatRet(500, '失败');
    }

    public function import(BaseRequests $request)
    {
        try {
            $productSpecImport = new ProductSpecImport(new ProductService);
            app('excel')->import($productSpecImport, $request->file('file'));
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
            info('货品规格导入失败', ["message" => $exception->getMessage()]);
            return formatRet(500, '导入失败');
        }

    }
}
