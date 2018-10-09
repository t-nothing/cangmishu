<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Warehouse;
use App\Models\WarehouseArea;
use App\Models\OrderType;

class EditOrderTypeTest extends TestCase
{
    use DatabaseTransactions;

    //修改出库单分类参数缺失
    public function testEditOrderTypeLack()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            'POST',
            '/orderType', [
            'name' => '出库分类ABD'
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

    //修改出库单分类 出库单分类不存在
    public function testEditOrderTypeNoexist()
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
            'POST',
            '/orderType', [
            'order_type_id' => 9999999,
            'name' => '出库分类EFG',
            'area_id' => $warehousearea->id,
            'is_enabled' => 1,
            'is_partial' => 1
        ])->seeJson([
            'status' => 404
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            'status',
            'msg',
            'data'
        ]);
    }

    //修改出库单分类 出库单name已存在
    public function testEditOrderTypeExistName()
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

        factory('App\Models\OrderType')->create([
            'name' => '出库分类BBB',
            "warehouse_id" => $warehouse->id,
            'area_id' => $warehousearea->id,
            'is_enabled' => 0,
            'is_partial' => 0
        ]);
        $ordertype = OrderType::orderBy('id', 'desc')->first();

        $this->json(
            'POST',
            '/orderType', [
            'order_type_id' => $ordertype->id,
            'name' => '出库分类AAA',
            'area_id' => $warehousearea->id,
            'is_enabled' => 1,
            'is_partial' => 1
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

    //修改出库单分类 货区不存在
    public function testEditOrderTypeNoexistWarehoseArea()
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
            'POST',
            '/orderType', [
            'order_type_id' => $ordertype->id,
            'name' => '出库分类ABD',
            'area_id' => 9999999,
            'is_enabled' => 1,
            'is_partial' => 1
        ])->seeJson([
            'status' => 404
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            'status',
            'msg',
            'data'
        ]);
    }

    //修改出库单分类 仓库无此货区
    public function testEditOrderTypeNoexistWarehoseAreaInWarehose()
    {
        $this->withoutMiddleware();
        $otheruser = $this->fakerOtherUser();//其他用户登录
        //其他人仓库
        factory('App\Models\Warehouse')->create([
            "owner_id" => $otheruser->id,
            'type' => 2,//私有仓库
            'status' => 2,//审核通过
            'is_used' => 0
        ]);
        //其他人仓库货区
        $otherwarehouse = Warehouse::orderBy('id', 'desc')->first();

        factory('App\Models\WarehouseArea')->create([
            "warehouse_id" => $otherwarehouse->id,
            'is_enabled' => 1
        ]);
        $otherwarehousearea = WarehouseArea::orderBy('id', 'desc')->first();
        //自己登录
        $user = $this->fakerUser();
        //自己仓库
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
            'POST',
            '/orderType', [
            'order_type_id' => $ordertype->id,
            'name' => '出库分类ABD',
            'warehouse_id' => $warehouse->id,//自己仓库
            'area_id' => $otherwarehousearea->id,//他人货区
            'is_enabled' => 1,
            'is_partial' => 1
        ])->seeJson([
            'status' => 404
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            'status',
            'msg',
            'data'
        ]);
    }

    //修改出库单分类成功
    public function testEditOrderType()
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
            'POST',
            '/orderType', [
            'order_type_id' => $ordertype->id,
            'name' => '出库分类BBB',
            'area_id' => $warehousearea->id,
            'is_enabled' => 1,
            'is_partial' => 1
        ])->seeJson([
            'status' => 0
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            'status',
            'msg',
            'data'
        ])->seeInDatabase(
            'order_type', [
            'id' => $ordertype->id,
            'name' => '出库分类BBB',
            'area_id' => $warehousearea->id,
            'is_enabled' => 1,
            'is_partial' => 1
        ]);
    }
}
