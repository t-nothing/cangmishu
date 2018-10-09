<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Category;

class DeleteCategoryTest extends TestCase
{
    use DatabaseTransactions;

    //删除商品分类成功
    public function testDeleteCategory()
    {
        $this->withoutMiddleware();

        factory('App\Models\Category')->create([
            'name_cn' => '测试分类123123123',
        ]);
        $category = Category::orderBy('id', 'desc')->first();

        $this->json(
            "DELETE",
            "/admin/category", [
            'category_id' => $category->id
        ])->seeJson([
            'status' => 0
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ])->notSeeInDatabase(
            "category", [
            "id" => $category->id,
            "name_cn" => "测试分类123123123"
        ]);
    }

    //删除分类 分类不存在
    public function testDeleteCategoryNoExistId()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "DELETE",
            "/admin/category", [
            'category_id' => 99999999
        ])->seeJson([
            "status" => 404
        ])->seeStatusCode(
            404
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ]);
    }

    // 删除分类 分类下存在商品
    public function testDeleteCategoryExistProduct()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        factory('App\Models\Category')->create([
            "name_cn" => "测试删除分类001"
        ]);
        $category = Category::orderBy('id', 'desc')->first();

        factory('App\Models\Product')->create([
            'name_en' => "apple",
            'category_id' => $category->id,
            'hs_code' => "ipone7",
            'storage_compartment' => 19,
            'origin' => "陕西",
            'display_link' => "https://www.baidu.com",
            'remark' => "苹果七个",
            'photos' => "https://www.baidu.com/img/bd_logo1.png",
            "owner_id" => $user->id
        ]);
        $this->json(
            "DELETE",
            "/admin/category", [
            'category_id' => $category->id
        ])->seeJson([
            "status" => 500
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ]);
    }
}
