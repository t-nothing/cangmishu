<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductSpecController extends Controller
{
    public function __construct()
    {
        $this->warehouse = app('auth')->warehouse();
    }

	/**
     * 货品规格 - 新增
     */
	public function list($product_id)
    {
    	$product = Product::ofWarehouse($warehouse->id)->find($product_id);

    	if(app('auth')->roleId() == WarehouseEmployee::ROLE_RENTER){
    		$product->whose(app('auth')->id());
    	}

        if (! $product) {
            return formatRet(404, '货品不存在', [], 404);
        }

        return formatRet(0, '', $product->specs->toArray());
    }

	/**
     * 货品规格 - 新增
     */
	public function store(Request $request)
    {
        $this->validate($request, [
            'product_id'     => 'required|integer|min:1',
            'name_cn'        => 'required|string|max:255',
            'name_en'        => 'required|string|max:255',
            'net_weight'     => 'present|numeric',
            'gross_weight'   => 'present|numeric',
            'relevance_code' => 'required|string|max:255',
        ]);

        $user_id = Auth::id();

        $spec = new ProductSpec;

        $product = Product::whose(Auth::id())->find($request->product_id);
        if (empty($product)) {
            return formatRet(404, '货品不存在', [], 404);
        }

        $this->validate($request, [
            'relevance_code' => Rule::unique($spec->getTable())->where(function ($query) use ($request, $user_id) {
                return $query->where('owner_id', $user_id)
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
        $spec->owner_id       = $user_id;
        $spec->save();

        return formatRet(0);
    }

    /**
     * 货品规格 - 修改
     *
     * @author liusen
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'spec_id'        => 'required|integer|min:1',
            'name_cn'        => 'required|string|max:255',
            'name_en'        => 'required|string|max:255',
            'net_weight'     => 'present|numeric',
            'gross_weight'   => 'present|numeric',
            'relevance_code' => 'required|string|max:255',
        ]);

        $user_id = Auth::id();

        if (! $spec = ProductSpec::whose(Auth::id())->find($request->spec_id)) {
            return formatRet(404, '商品规格不存在', [], 404);
        }

        $this->validate($request, [
            'relevance_code' => Rule::unique($spec->getTable())->ignore($spec->id)->where(function ($query) use ($request, $user_id) {
                return $query->where('owner_id', $user_id)
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
	public function delete(Request $request)
    {
    	$this->validate($request, [
            'spec_id' => 'required|integer|min:1',
        ]);

        if (! $spec = ProductSpec::find($request->spec_id)) {
            return formatRet(404, '商品规格不存在', [], 404);
        }

        if ($spec->delete()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }
}
