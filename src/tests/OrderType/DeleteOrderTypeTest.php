<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Warehouse;
use App\Models\WarehouseArea;
use App\Models\OrderType;

class DeleteOrderTypeTest extends TestCase
{
    use DatabaseTransactions;

    // 缺少参数
    public function testDeleteOrderTypeLack()
    {
        $this->withoutMiddleware();

        $this->json(
            "DELETE",
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

    // 出库单分类不存在
    public function testDeleteOrderTypeNoexist()
    {
        $this->withoutMiddleware();

        $this->json(
            "DELETE",
            "/orderType", [
                "order_type_id" => 9999999
            ]
        )->seeJson([
            "status" => 404
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ]);
    }

    //出库单分类已被使用
    public function testDeleteOrderTypeUsed()
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
        $ordertype = OrderType::orderBy('id', 'desc')->first();

        factory('App\Models\Order')->create([
            'warehouse_id' => $warehouse->id,
            'out_sn' => 'MES18904434113111',
            'status' => 1,
            'delivery_date' => '1526659200',
            'delivery_type' => 1,
            'owner_id' => $user->id,
            'express_num' => 'AAAAAAAA',
            'shipment_num' => 'BBBBBBBB',
            'order_type' => $ordertype->id
        ]);

        $this->json(
            "DELETE",
            "/orderType", [
                "order_type_id" => $ordertype->id
            ]
        )->seeJson([
            "status" => 500
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ]);
    }

    //出库单分类已被使用
    public function testDeleteOrderType()
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
        $ordertype = OrderType::orderBy('id', 'desc')->first();

        $this->json(
            "DELETE",
            "/orderType", [
                "order_type_id" => $ordertype->id
            ]
        )->seeJson([
            "status" => 0
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ])->notSeeInDatabase(
            "order_type", [
            'name' => '出库分类AAA',
            'area_id' => $warehousearea->id,
            'is_enabled' => 0,
            'is_partial' => 0,
            'deleted_at' => null
        ]);
    }

}

