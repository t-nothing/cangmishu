<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\WarehouseArea;
use App\Models\Warehouse;

class ListWarehouseAreaTest extends TestCase
{
    use DatabaseTransactions;

    //缺少必要参数
    public function testListLackParams()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();

        $this->json(
            "GET",
            "/warehouseArea",
            [
                //'warehouse_id' => 1,
                'page' => '',
                'page_size' => '',
                'is_enabled' => 1,
            ]
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)->seeJsonStructure(
            [
                "status",
                "msg",
                "data" => [
                    'warehouse_id',
                ]
            ]
        );
    }

    //列表查询成功
    public function testListSucc()
    {
        $this->withoutMiddleware();

        $user = $this->fakerUser();

        factory('App\Models\Warehouse')->create(
            [
                "name_cn" => "测试仓库",
                "name_en" => "warehouse_1",
                "type" => 1,
                "code" => "abcd_01",
                "country" => "cn",
                "city" => "changsha",
                "street" => "some road",
                "postcode" => "410000abc",
                "status" => 2,
                'owner_id' => $user->id,
                "temperature" => 1,
                "area" => "2500",
                "contact_user" => "some people",
                "contact_number" => "12345678911",
                "contact_email" => "test@nle-tech.com"
            ]
        );

        $warehouse = Warehouse::orderBy("id", "desc")->first();

        factory('App\Models\WarehouseArea')->create(
            [
                'code' => 'unit1',
                'name_cn' => '测试仓库货区01',
                'name_en' => 'warehouse_01',
                'warehouse_id' => $warehouse->id,
                'temperature' => 1,
                'is_enabled' => 1,
                'functions' => ["1", "2"],
                'remark' => '备注',
            ]
        );

       // $warehouseAre = WarehouseArea::orderBy("id", 'desc')->first();

        $this->json(
            "GET",
            "/warehouseArea/",
            [
//                       'warehouse_id' => 1,
                'warehouse_id' => $warehouse->id,//int(75)
            ]
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)->seeJsonStructure(
            [
                "status",
                "msg",
                "data" => [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total',
                ]
            ]
        );
    }
}
