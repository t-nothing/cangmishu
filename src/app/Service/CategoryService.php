<?php
namespace  App\Services\Service;

use App\Models\Category;

class CategoryService
{
    public function getCategoryIdByNameCn($name) {
        $category = Category::where('name_cn',$name)->first();
        if(!$category) {
            return 0;
        }
        return $category->id;
    }
}