<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\WarehouseArea;
use App\Models\Warehouse;

class DeleteWarehouseAreaTest extends TestCase
{
    use DatabaseTransactions;

    /*
     * * 货区删除失败
     * @货区id不存在
     * @author xs
     * */
    public function testDeleteNoExist()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "DELETE",
            "/warehouseArea",
            ["area_id" => 999999]
        )->seeJson(
            [
                "status" => 404
            ]
        )->seeStatusCode(404)->seeJsonStructure(
            [
                "status",
                "msg",
                "data"
            ]
        );
    }

    /*
     *货区删除成功
     * */
    public function testDeleteSucc()
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
            "DELETE",
            "/warehouseArea",
            ["area_id" => $warehouseArea->id]
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)->seeJsonStructure(
            [
                "status",
                "msg",
                "data"
            ]
        );
    }

}