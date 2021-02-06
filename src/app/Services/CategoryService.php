<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace  App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    var $warehouse_id = 0;

    public function setWarehouseId($id) {
        $this->warehouse_id = $id;
        return $this;
    }
    /**
     * 缓存KEY
     **/
    public function getCacheTagName($warehouseId){
        return sprintf("category:%s", $warehouseId);
    }

    /**
     * 缓存重建
     **/
    public function cacheBuild($modelData){
        Cache::forget('key');
        $key = $this->getCacheTagName($modelData->warehouse_id);
        Cache::tags($key)->flush();
    }

    public function getCacheList(){
        
    }

    public function getCategoryIdByNameCn($name) {
        $category = Category::where('name_cn',$name)->first();
        if(!$category) {
            return 0;
        }
        return $category->id;
    }
}