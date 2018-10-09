<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Rules\PageSize;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\UserCategoryWarning;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->warehouse = app('auth')->warehouse();
    }

    public function list(Request $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'warehouse_id' => 'required|integer',
            'is_enabled'   => 'boolean',
        ]);

        $category = Category::with('feature:id,name_cn,name_en')->ofWarehouse($this->warehouse->id);
	

        $request->filled('is_enabled') AND
            $category->where('is_enabled', $request->is_enabled);

        $categories = $category->paginate($request->input('page_size'));

        return formatRet(0, '', $categories->toArray());
    }

    public function show($id)
    {
        $warehouse = app('auth')->warehouse();

        $category = Category::ofWarehouse($this->warehouse->id)->findOrFail($id);

        return formatRet(0, '', $category->toArray());
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'warehouse_id'                 => 'required|integer',
            'warehouse_feature_id'         => 'required|integer',
            'name_cn'                      => 'required|string|max:255',
            'name_en'                      => 'required|string|max:255',
            'is_enabled'                   => 'required|boolean',
            'need_expiration_date'         => 'required|boolean',
            'need_production_batch_number' => 'required|boolean',
            'need_best_before_date'        => 'required|boolean',
        ]);

        $category = new Category;

        $this->validate($request, [
            'name_cn' => Rule::unique('category', 'name_cn')->where(function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id);
            }),
            'name_en' => Rule::unique('category', 'name_en')->where(function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id);
            }),
            'warehouse_feature_id' => Rule::exists('warehouse_feature', 'id')->where(function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id);
            }),
        ]);

        $category->warehouse_id                 = $this->warehouse->id;
        $category->warehouse_feature_id         = $request->warehouse_feature_id;
        $category->name_cn                      = $request->name_cn;
        $category->name_en                      = $request->name_en;
        $category->is_enabled                   = $request->is_enabled;
        $category->need_expiration_date         = $request->need_expiration_date;
        $category->need_production_batch_number = $request->need_production_batch_number;
        $category->need_best_before_date        = $request->need_best_before_date;

	DB::beginTransaction();
	try {
		$category->save();
		$set_data = User::where('id', app('auth')->guard()->user()->getAuthIdentifier())->first();
		$userCategory = new  UserCategoryWarning();
		$userCategoryData = [
			'user_id' => app('auth')->id(),
			'category_id' => $category->id,
			'warning_stock'  => $set_data['default_warning_stock']
		];
		//用户分类预警新增
		$userCategory::binds($userCategory,$userCategoryData);
		$userCategory->save();

		DB::commit();
	} catch (\Exception $e) {
		DB::rollback();
		return formatRet(500, '失败'); 
	 }
        return formatRet(0);
    }

    public function edit(Request $request)
    {
        $this->validate($request, [
            'id'                           => 'required|integer|min:1',
            'warehouse_feature_id'         => 'required|integer',
            'name_cn'                      => 'required|string|max:255',
            'name_en'                      => 'required|string|max:255',
            'is_enabled'                   => 'required|boolean',
            'need_expiration_date'         => 'required|boolean',
            'need_production_batch_number' => 'required|boolean',
            'need_best_before_date'        => 'required|boolean',
        ]);

        $category = Category::ofWarehouse($this->warehouse->id)->findOrFail($request->id);

        $this->validate($request, [
            'name_cn' => Rule::unique('category', 'name_cn')->ignore($category->id)->where(function ($query) use ($category) {
                return $query->where('warehouse_id', $category->warehouse_id);
            }),
            'name_en' => Rule::unique('category', 'name_en')->ignore($category->id)->where(function ($query) use ($category) {
                return $query->where('warehouse_id', $category->warehouse_id);
            }),
            'warehouse_feature_id' => Rule::exists('warehouse_feature', 'id')->where(function ($query) use ($category) {
                return $query->where('warehouse_id', $category->warehouse_id);
            }),
        ]);

        $category->warehouse_feature_id         = $request->warehouse_feature_id;
        $category->name_cn                      = $request->name_cn;
        $category->name_en                      = $request->name_en;
        $category->is_enabled                   = $request->is_enabled;
        $category->need_expiration_date         = $request->need_expiration_date;
        $category->need_production_batch_number = $request->need_production_batch_number;
        $category->need_best_before_date        = $request->need_best_before_date;

        if (! $category->save()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ]);

        $category = Category::withCount('products')->ofWarehouse(app('auth')->warehouse()->id)->findOrFail($request->id);

        if ($category->products_count > 0) {
            return formatRet(500, '无法删除，分类下有商品');
        }

        if (! $category->delete()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }
}
