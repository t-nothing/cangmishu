<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Warehouse;
use App\Models\WarehouseArea;

class ListOrderTypeTest extends TestCase
{
    use DatabaseTransactions;

    // 出库单类型列表缺少参数
    public function testListOrderTypeLack()
    {
        $this->withoutMiddleware();

        $this->json(
            "GET",
            "/orderType"
        )->seeJson([
            "status" => 422
        ])->seeStatusCode(
            422
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ]);
    }

    // 出库单类型列表
    public function testListOrderType()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();

        factory('App\Models\Warehouse')->create([
            "owner_id" => $user->id,
            'type' => 2,//私有仓库
            'status' => 2,//审核通过
            'is_used' => 0
        ]);
        $warehouse = Warehouse::orderBy('id', 'desc')->first();

        factory('App\Models\WarehouseArea')->create([
            "warehouse_id" => $warehouse->id,
            'is_enabled' => 1
        ]);
        $warehousearea = WarehouseArea::orderBy('id', 'desc')->first();

        factory('App\Models\OrderType')->create([
            'name' => '出库分类AAA',
            "warehouse_id" => $warehouse->id,
            'area_id' => $warehousearea->id,
            'is_enabled' => 0,
            'is_partial' => 0
        ]);

        $this->json(
            "GET",
            "/orderType?warehouse_id=" . $warehouse->id
        )->seeJson([
            "status" => 0
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            "status",
            "msg",
            "data" => [
                "data" => [
                    0 => [
                        "id",
                        "name",
                        "area_id",
                        "warehouse_area"
                    ]
                ]
            ]
        ]);
    }

}
