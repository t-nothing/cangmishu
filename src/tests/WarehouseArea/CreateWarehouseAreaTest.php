<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Warehouse;
use App\Models\WarehouseArea;

class CreateWarehouseAreaTest extends TestCase
{
    use DatabaseTransactions;

    //创建缺少参数
    public function testCreateLackParams()
    {
        $this->withoutMiddleware();

        $this->json(
            'PUT',
            '/warehouseArea',
            [
                'warehouse_id' => '1',
            ]
        )->seeJson(
            [
                'status' => 422
            ]
        )->seeStatusCode(422)->seeJsonStructure(
            [
                'status',
                'msg',
                'data' => [
                    'code',
                    'name_cn',
                    'name_en',
                    'temperature',
                    'is_enabled',
                ]
            ]
        );
    }

    // 此仓库下的货区code已经存在
    public function testCreateWarehouseAreaExist()
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
                'functions' => '[1,2]',
                'remark' => '备注',
            ]
        );

        $warehouseArea = WarehouseArea::orderBy("id", "desc")->first();


        $this->json(
            "PUT",
            "/warehouseArea",
            [
                'code' => $warehouseArea->code,
                "name_cn" => '测试仓库货区01_edit',
                "name_en" => 'warehouse_01_edit',
                'temperature' => '3',
                'is_enabled' => 0,
                'functions' => [1, 2, 3],
                'remark' => '备注_edit',
                "area_id" => 999,
                'warehouse_id' => $warehouse->id
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
                ]
            ]
        );

    }

    public function testCreateSucc()
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


        $r = $this->json(
            "PUT",
            "/warehouseArea",
            [
                'warehouse_id' => $warehouse->id,
                'code' => 'unit1',
                "name_cn" => '测试仓库货区01',
                "name_en" => 'warehouse_01',
                'temperature' => '1',
                'is_enabled' => 1,
                'functions' => [1, 2],
                'remark' => '备注',
            ]
        )->seeJson(
            [
                'status' => 0
            ]
        )->seeStatusCode(200)->seeJsonStructure(
            [
                "status",
                "msg",
                "data" => [
                ]
            ]
        );

        $r = $this->seeInDatabase(
            'warehouse_area',
            [
                'warehouse_id' => $warehouse->id,
                'code' => 'unit1',
                "name_cn" => '测试仓库货区01',
                "name_en" => 'warehouse_01',
                'temperature' => '1',
                'is_enabled' => 1,
                'functions' => '[1,2]',
                'remark' => '备注',
            ]
        );
    }
}