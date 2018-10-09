<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Warehouse;
use App\Models\WarehouseArea;

class CreateShelfTest extends TestCase
{
    use DatabaseTransactions;

    // 添加货架 缺少参数
    public function testCreateShelfLack()
    {
        $this->withoutMiddleware();

        $this->json(
            "PUT",
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

    // 添加货架 仓库不存在
    public function testCreateShelfNoexistWarehouse()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();

        factory('App\Models\Warehouse')
            ->create([
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

        $this->json(
            "PUT",
            "/shelf", [
            'code' => 'S01',
            'warehouse_id' => 999999,
            'warehouse_area_id' => $warehousearea->id,
            'capacity' => 10,
            'is_enabled' => 1
        ])->seeJson([
            "status" => 404
        ])->seeStatusCode(
            404
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ]);
    }

    // 添加货架 货区不存在
    public function testCreateShelfNoexistWarehouseArea()
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

        $this->json(
            "PUT",
            "/shelf", [
            'code' => 'S01',
            'warehouse_id' => $warehouse->id,
            'warehouse_area_id' => 999999,
            'capacity' => 10,
            'is_enabled' => 1
        ])->seeJson([
            "status" => 404
        ])->seeStatusCode(
            404
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ]);
    }

    // 添加货架 Code已存在
    public function testCreateShelfExistCode()
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

        factory('App\Models\Shelf')->create([
            'code' => 'S01',
            'warehouse_id' => $warehouse->id,
            'warehouse_area_id' => $warehousearea->id,
            'is_enabled' => 1
        ]);

        $this->json(
            "PUT",
            "/shelf", [
            'code' => 'S01',
            'warehouse_id' => $warehouse->id,
            'warehouse_area_id' => $warehousearea->id,
            'capacity' => 10,
            'is_enabled' => 1
        ])->seeJson([
            "status" => 422
        ])->seeStatusCode(
            422
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ]);
    }

    // 添加货架 成功
    public function testCreateShelf()
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

        $this->json(
            "PUT",
            "/shelf", [
            'code' => 'S01',
            'warehouse_id' => $warehouse->id,
            'warehouse_area_id' => $warehousearea->id,
            'capacity' => 100,
            'is_enabled' => 1
        ])->seeJson([
            'status' => 0
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            "status",
            "msg"
        ])->seeInDatabase(
            'shelf', [
            'code' => 'S01',
            'warehouse_id' => $warehouse->id,
            'warehouse_area_id' => $warehousearea->id,
            'capacity' => 100,
            'is_enabled' => 1
        ]);
    }

}
