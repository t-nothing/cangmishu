<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Category;

class EditCategoryTest extends TestCase
{
    use DatabaseTransactions;

    // 修改商品分类成功
    public function testEditCategory()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        factory('App\Models\Category')->create([
            'name_cn' => '测试分类123123123',
        ]);
        $category = Category::orderBy('id', 'desc')->first();
        $this->json(
            'POST',
            '/admin/category', [
            'category_id' => $category->id,
            'name_cn' => '测试分类1112222222',
            'name_en' => 'test111',
            'is_enabled' => 1,
            'need_production_batch_number' => 1,
            'need_expiration_date' => 1
        ])->seeJson([
            'status' => 0
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            'status',
            'msg',
            'data'
        ])->seeInDatabase(
            'category', [
            "id" => $category->id,
            'name_cn' => '测试分类1112222222',
            'name_en' => 'test111',
            'is_enabled' => 1,
            'need_production_batch_number' => 1,
            'need_expiration_date' => 1
        ]);
    }

    // 修改商品分类参数缺失
    public function testEditCategoryLack()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            'POST',
            '/admin/category', [
            'name_cn' => '测试分类02'
        ])->seeJson([
            'status' => 422
        ])->seeStatusCode(
            422
        )->seeJsonStructure([
            'status',
            'msg',
            'data'
        ]);
    }
}
