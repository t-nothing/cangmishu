<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CreateCategoryTest extends TestCase
{
    use DatabaseTransactions;

    //创建商品分类参数缺失
    public function testCreateCatetoryLack()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            'PUT',
            '/admin/category', [
            'name_cn' => '测试分类123123123'
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

    //创建商品分类成功
    public function testCreateCatetory()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            'PUT',
            '/admin/category', [
                'name_cn' => '测试分类123123123',
                'name_en' => 'test',
                'is_enabled' => 0,
                'need_production_batch_number' => 0,
                'need_expiration_date' => 0]
        )->seeJson([
            'status' => 0
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            'status',
            'msg',
            'data'
        ])->seeInDatabase(
            'category', [
            'name_cn' => '测试分类123123123',
            'name_en' => 'test',
            'is_enabled' => 0,
            'need_production_batch_number' => 0,
            'need_expiration_date' => 0
        ]);
    }

}
