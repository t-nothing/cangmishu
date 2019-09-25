<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\UserCategoryWarning;
use App\Rules\PageSize;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(BaseRequests $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize(),
            'is_enabled'   => 'boolean',
            'no_pager'     => 'boolean',
            'warehouse_id' => 'required|integer|min:1',
        ]);

        $categories = Category::with('feature:id,name_cn,name_en')
                    ->ofWarehouse($request->warehouse_id)
                    ->where('owner_id',Auth::ownerId())
                    ->when($request->filled('is_enabled'),function($q)use($request) {
                        $q->where('is_enabled', $request->is_enabled);
                    })
                    ->orderBy('id','ASC');
        //如果需要分页
        if(!$request->filled('no_pager', 0)) {
            $categories = $categories->paginate($request->input('page_size',10));
        }
        else {
            $categories = $categories->get();
        }
                    

        return formatRet(0, '', $categories->toArray());
    }


    public function store(CreateCategoryRequest $request)
    {
        app('log')->info('新增货品分类', $request->all());
        DB::beginTransaction();
        try{
            $data = $request->all();
            $data["name_en"] = $request->name_cn;
            $data = array_merge($data, ['owner_id' =>Auth::ownerId(), 'warehouse_id'=>$request->warehouse_id]);
            $category = Category::create($data);
            DB::commit();
            return formatRet(0);
        }catch (\Exception $e){
            DB::rollBack();
            app('log')->error('新增货品分类失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.productCategoryAddFailed"));
        }
    }

    public function update(UpdateCategoryRequest $request,$id)
    {
        app('log')->info('编辑货品分类', ['id'=>$id]);
        try{
            $data = $request->all();
            $data["name_en"] = $request->input('name_en', $request->name_cn);
            $request->modelData->update($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('编辑货品分类失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.productCategoryUpdateFailed"));
        }
    }

    public function destroy($category_id)
    {
        app('log')->info('删除货品分类',['id'=>$category_id]);
        $category = Category::find($category_id);
        if(!$category){
            return formatRet(500, trans("message.productCategoryNotExist"));
        }
        if ($category->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }

        $product_count = $category->products()->count();
        if($product_count){
            return formatRet(500, trans("message.productCategoryCannotDelete"));
        }
        try{
            $category->delete();
            UserCategoryWarning::where('category_id',$category_id)->forceDelete();
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('删除货品分类失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("productCategoryDeleteFailed"));
        }
    }
}
