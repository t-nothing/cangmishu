<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Warehouse;
use App\Models\WarehouseArea;
use App\Models\Shelf;

class EditShelfTest extends TestCase
{
    use DatabaseTransactions;

    // 修改货架 缺少参数
    public function testEditShelfLack()
    {
        $this->withoutMiddleware();
        $this->json(
            "POST",
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

    //修改货架 货区不存在
    public function testEditShelfNoexistWarehouseArea()
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
        $shelf = Shelf::orderBy('id', 'desc')->first();

        $this->json(
            "POST",
            "/shelf", [
            'shelf_id' => $shelf->id,
            'code' => 'S02',
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

    //修改货架 货架不存在
    public function testEditShelfNoexistShelf()
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
            "POST",
            "/shelf", [
            'shelf_id' => 999999,
            'code' => 'S02',
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

    // 修改货架 Code已存在
    public function testEditShelfExistCode()
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

        factory('App\Models\Shelf')->create([
            'code' => 'S02',
            'warehouse_id' => $warehouse->id,
            'warehouse_area_id' => $warehousearea->id,
            'is_enabled' => 1
        ]);
        $shelf = Shelf::orderBy('id', 'desc')->first();

        $this->json(
            "POST",
            "/shelf", [
            'shelf_id' => $shelf->id,
            'code' => 'S01',
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

    // 修改货架 成功
    public function testEditShelf()
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
        $shelf = Shelf::orderBy('id', 'desc')->first();

        $this->json(
            "POST",
            "/shelf", [
            'shelf_id' => $shelf->id,
            'code' => 'S01',
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
            'warehouse_area_id' => $warehousearea->id,
            'capacity' => 100,
            'is_enabled' => 1
        ]);
    }

}
