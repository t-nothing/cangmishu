<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Category;

class EditCategoryWarningTest extends TestCase
{
    use DatabaseTransactions;

    //保存商品库存报警信息
    public function testSaveStockStore()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();

        factory('App\Models\Category')->create([
            "name_cn" => "测试保存商品分类001",
            "warning_stock" => 10,
            "parent_id" => 0
        ]);
        $category = Category::orderBy('id', 'desc')->first();
        $this->json(
            "POST",
            "/warning/stock", [
                "default_warning_stock" => 100,
                "warning_email" => "85417933@qq.com",
                "warning_data" => array(
                    0 => array(
                        "category_id" => $category->id,
                        "warning_stock" => 11,
                    )
                )]
        )->seeJson([
            "status" => 0
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ])->seeInDatabase(
            'user', [
            'id' => $user->id,
            'default_warning_stock' => 100,
            'warning_email' => '85417933@qq.com'
        ])->seeInDatabase(
            'user_category_warning', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'warning_stock' => 11,
        ]);
    }

}
