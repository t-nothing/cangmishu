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
        ]);

        $categories = Category::with('feature:id,name_cn,name_en')
                    ->ofWhose(Auth::ownerId())
                    ->when($request->filled('is_enabled'),function($q)use($request) {
                        $q->where('is_enabled', $request->is_enabled);
                    })
                    ->paginate($request->input('page_size',10));

        return formatRet(0, '', $categories->toArray());
    }


    public function store(CreateCategoryRequest $request)
    {
        app('log')->info('新增货品分类', $request->all());
        DB::beginTransaction();
        try{
            $data = $request->all();
            $data = array_merge($data, ['owner_id' =>Auth::ownerId()]);
            $category = Category::create($data);

            //新增库存报警
            $owner = User::find(Auth::ownerId());
            $warn_stock= $owner->default_warning_stock;
            $userCategoryData = [
                'user_id' => Auth::ownerId(),
                'category_id' => $category->id,
                'warning_stock'  => $warn_stock
            ];
            UserCategoryWarning::create($userCategoryData);
            DB::commit();
            return formatRet(0);
        }catch (\Exception $e){
            DB::rollBack();
            app('log')->error('新增货品分类失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"新增货品分类失败");
        }
    }

    public function update(UpdateCategoryRequest $request,$category_id)
    {
        app('log')->info('编辑货品分类', ['category_id'=>$category_id]);
        try{
            $data = $request->all();
            Category::where('id',$category_id)->update($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('编辑货品分类失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"编辑货品分类失败");
        }
        return formatRet(0);
    }

    public function destroy($category_id)
    {
        app('log')->info('删除货品分类',['id'=>$category_id]);
        $category = Category::find($category_id);
        if(!$category){
            return formatRet(500,"货品分类不存在");
        }
        if ($category->owner_id != Auth::ownerId()){
            return formatRet(500,"没有权限");
        }
        try{
            Category::where('id',$category_id)->delete();
            UserCategoryWarning::where('category_id',$category_id)->forceDelete();
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('删除货品分类失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"删除货品分类失败");
        }
    }
}
