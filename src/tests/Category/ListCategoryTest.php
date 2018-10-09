<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ListCategoryTest extends TestCase
{
    use DatabaseTransactions;

    // 分类列表
    public function testListCategory()
    {
        $this->withoutMiddleware();

        factory('App\Models\Category')->create([
            'name_cn' => '测试分类123123123',
        ]);

        $this->json(
            "GET",
            "/category"
        )->seeJson([
            "status" => 0
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ]);
    }

}
