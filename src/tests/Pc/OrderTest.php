<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Warehouse;
use App\Models\WarehouseArea;
use App\Models\OrderType;
use App\Models\Order;

class OrderTest extends TestCase
{
    use DatabaseTransactions;

    // 出库单列表成功 by express_num
    public function testListOrderByExpressnum()
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

        factory('App\Models\OrderType')->create([
            "warehouse_id" => $warehouse->id,
            'area_id' => $warehousearea->id,
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
            "GET",
            "/pc/order?perPage=20&warehouse_id=" . $warehouse->id . "&express_num=AAAAAAAA"
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
                        "warehouse_id",
                        "out_sn",
                        "status",
                        "delivery_date",
                        "delivery_type",
                        "owner_id",
                        "express_num"
                    ]
                ]
            ]
        ]);
    }

    // 出库单列表成功 by shipment_num
    public function testListOrderByShipmentnum()
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


        factory('App\Models\OrderType')->create([
            "warehouse_id" => $warehouse->id,
            'area_id' => $warehousearea->id,
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
            "GET",
            "/pc/order?perPage=20&warehouse_id=" . $warehouse->id . "&shipment_num=BBBBBBBB"
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
                        "warehouse_id",
                        "out_sn",
                        "status",
                        "delivery_date",
                        "delivery_type",
                        "owner_id",
                        "express_num"
                    ]
                ]
            ]
        ]);
    }

    // 出库单列表成功by keyword
    public function testListOrderByKeyword()
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

        factory('App\Models\OrderType')->create([
            "warehouse_id" => $warehouse->id,
            'area_id' => $warehousearea->id,
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
            "GET",
            "/pc/order",
            [
                'warehouse_id' => $warehouse->id,
                'keyword' => 'MES18904434113111',
            ]
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
                        "warehouse_id",
                        "out_sn",
                        "status",
                        "delivery_date",
                        "delivery_type",
                        "owner_id",
                        "express_num"
                    ]
                ]
            ]
        ]);
    }

    // 出库单列表成功by delivery_date
    public function testListOrderByDeliverydate()
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

        factory('App\Models\OrderType')->create([
            "warehouse_id" => $warehouse->id,
            'area_id' => $warehousearea->id,
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
            "GET",
            "/pc/order",
            [
                'warehouse_id' => $warehouse->id,
                'start_time' => '2018-05-18 00:00:00',
                'over_time' => '2018-05-19 00:00:00',
            ]
        )->seeJson([
            "status" => 0
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            "status",
            "msg",
            "data" => [
                "data" => [
                    [
                        "id",
                        "warehouse_id",
                        "out_sn",
                        "status",
                        "delivery_date",
                        "delivery_type",
                        "owner_id",
                        "express_num"
                    ]
                ]
            ]
        ]);
    }

    // 出库单列表成功by postcode
    public function testListOrderByPostcode()
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

        factory('App\Models\OrderType')->create([
            "warehouse_id" => $warehouse->id,
            'area_id' => $warehousearea->id,
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
            'receiver_postcode' => 'QWERT',
            'order_type' => $ordertype->id
        ]);

        $this->json(
            "GET",
            "/pc/order?perPage=20&warehouse_id=" . $warehouse->id . "&postcode=QWERT"
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
                        "warehouse_id",
                        "out_sn",
                        "status",
                        "delivery_date",
                        "delivery_type",
                        "owner_id",
                        "express_num"
                    ]
                ]
            ]
        ]);
    }
}
