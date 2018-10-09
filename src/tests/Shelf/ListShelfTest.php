<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Warehouse;
use App\Models\WarehouseArea;

class ListShelfTest extends TestCase
{
    use DatabaseTransactions;

    // 货架列表缺少参数
    public function testListShelfLack()
    {
        $this->withoutMiddleware();

        $this->json(
            "GET",
            "/shelf"
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

    // 货架列表成功
    public function testListShelf()
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
            'is_enabled' => 1,
            'temperature' => 1,
            'functions' => ["1", "2"]
        ]);
        $warehousearea = WarehouseArea::orderBy('id', 'desc')->first();

        factory('App\Models\Shelf')->create([
            'code' => 'S01',
            'warehouse_id' => $warehouse->id,
            'warehouse_area_id' => $warehousearea->id,
            'is_enabled' => 1
        ]);

        $this->json(
            "GET",
            "/shelf?warehouse_id=" . $warehouse->id
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
                        "code",
                        "warehouse_area"
                    ]
                ]
            ]
        ]);
    }

}