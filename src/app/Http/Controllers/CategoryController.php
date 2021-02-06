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
            'no_feature'   => 'boolean',
        ]);
        app('log')->info('拉取商品分类', $request->all());

        $categories = Category::ofWarehouse(app('auth')->warehouse()->id);

        $categories = $categories
                    ->where('owner_id',Auth::ownerId())
                    ->when($request->filled('is_enabled'),function($q)use($request) {
                        $q->where('is_enabled', $request->is_enabled);
                    })
                    ->when(!$request->filled('no_feature', 0),function($q)use($request) {
                        $q->with('feature:id,name_cn,name_en');
                    })
                    ->orderBy('id','ASC');
        //如果需要分页
        if(!$request->filled('no_pager')) {
            $categories = $categories->paginate($request->input('page_size',10));
        }
        else {        

            $categories = $categories->get();
        }
                    

        return formatRet(0, '', $categories->toArray());
    }

    public function show( BaseRequests $request,$id)
    {
        $id = intval($id);
        $category = Category::ofWarehouse(app('auth')->warehouse()->id)->find($id);
        if(!$category){
            return formatRet(500, trans("message.productCategoryNotExist"));
        }
        if ($category->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }

        return formatRet(0, '', $category->toArray());
       
    }

    public function store(CreateCategoryRequest $request)
    {
        app('log')->info('新增货品分类', $request->all());
        DB::beginTransaction();
        try{
            $data = $request->all();
            $data["name_en"] = $request->name_cn;
            $data = array_merge($data, ['owner_id' =>Auth::ownerId(), 'warehouse_id'=>app('auth')->warehouse()->id]);
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
