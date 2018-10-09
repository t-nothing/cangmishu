<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Warehouse;
use App\Models\WarehouseArea;

class CreateOrderTypeTest extends TestCase
{
    use DatabaseTransactions;

    //创建出库单分类参数缺失
    public function testCreateOrderTypeLack()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            'PUT',
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

    //创建出库单分类 仓库不存在
    public function testCreateOrderTypeNoexistWarehose()
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
            'PUT',
            '/orderType', [
            'name' => '出库分类ABD',
            'warehouse_id' => 99999,
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

    //创建出库单分类 未拥有仓库的使用权
    public function testCreateOrderTypeNoauthorityWarehose()
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

        $this->fakerOtherUser();//其他用户登录
        $this->json(
            'PUT',
            '/orderType', [
            'name' => '出库分类ABD',
            'warehouse_id' => $warehouse->id,
            'area_id' => $warehousearea->id,
            'is_enabled' => 1,
            'is_partial' => 1
        ])->seeJson([
            'status' => 403
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            'status',
            'msg',
            'data'
        ]);
    }

    //创建出库单分类 货区不存在
    public function testCreateOrderTypeNoexistWarehoseArea()
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
            'PUT',
            '/orderType', [
            'name' => '出库分类ABD',
            'warehouse_id' => $warehouse->id,
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

    //创建出库单分类 仓库无此货区
    public function testCreateOrderTypeNoexistWarehoseAreaInWarehose()
    {
        $this->withoutMiddleware();
        $otheruser = $this->fakerOtherUser();//其他用户登录
        //其他人仓库
        factory('App\Models\Warehouse')->create([
                "owner_id" => $otheruser->id,
                'type' => 2,//私有仓库
                'status' => 2,//审核通过
                'is_used' => 0
            ]
        );
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

        $this->json(
            'PUT',
            '/orderType', [
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

    //穿件出库单分类 出库单name已存在
    public function testCreateOrderTypeExistName()
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
            'PUT',
            '/orderType', [
            'name' => '出库分类AAA',
            "warehouse_id" => $warehouse->id,
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

    //创建出库单分类成功
    public function testCreateOrderType()
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
            'PUT',
            '/orderType', [
            'name' => '出库分类AAA',
            'warehouse_id' => $warehouse->id,
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
            'name' => '出库分类AAA',
            'area_id' => $warehousearea->id,
            'is_enabled' => 1,
            'is_enabled' => 1
        ]);
    }
}
