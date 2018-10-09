<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Warehouse;
use App\Models\WarehouseArea;
use App\Models\Shelf;
use App\Models\Tray;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpec;


class DeleteShelfTest extends TestCase
{
    use DatabaseTransactions;

    // 删除货架 缺少参数
    public function testDeleteShelfLack()
    {
        $this->withoutMiddleware();

        $this->json(
            "DELETE",
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

    // 删除货架 货架不存在
    public function testDeleteShelfNoexistShelf()
    {
        $this->withoutMiddleware();

        $this->json(
            "DELETE",
            "/shelf", [
            "shelf_id" => 9999999
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

    // 删除货架 货架上存在商品在
    public function testDeleteShelfExistProduct()
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

        factory('App\Models\Tray')->create([
            'code' => 'S01',
            'shelf_id' => $shelf->id,
            'warehouse_id' => $warehouse->id,
            'status' => 1,
            'is_enabled' => 1
        ]);
        $tray = Tray::orderBy('id', 'desc')->first();

        factory('App\Models\Category')->create([
            'parent_id' => 0,
            'warning_stock' => 3,
            'name_cn' => '分类',
            'name_en' => 'ccc'
        ]);
        $category = Category::orderBy('id', 'desc')->first();

        factory('App\Models\Product')->create([
            'category_id' => $category->id,
            'hs_code' => 'hscode',
            'owner_id' => $user->id
        ]);
        $product = Product::orderBy('id', 'desc')->first();

        factory('App\Models\ProductSpec')->create([
            'product_id' => $product->id,
            'relevance_code' => 'JD001',
            'owner_id' => $user->id
        ]);
        $ProductSpec = ProductSpec::orderBy('id', 'desc')->first();

        factory('App\Models\ProductStock')->create([
            'spec_id' => $ProductSpec->id,
            'relevance_code' => 'JD001',
            'need_num' => 3,
            'pieces_num' => 1,
            'distributor_code' => 'ccc',
            'warehouse_id' => $warehouse->id,
            'tray_id' => $tray->id
        ]);

        $this->json(
            "DELETE",
            "/shelf", [
            "shelf_id" => $shelf->id
        ])->seeJson([
            "status" => 500
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ]);
    }

    // 删除货架成功
    public function testDeleteShelf()
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
            "DELETE",
            "/shelf", [
            "shelf_id" => $shelf->id
        ])->seeJson([
            "status" => 0
        ])->seeStatusCode(
            200
        )->seeJsonStructure([
            "status",
            "msg",
            "data"
        ])->notSeeInDatabase(
            "shelf", [
            "id" => $shelf->id,
            "code" => "S01",
            "warehouse_id" => $warehouse->id,
            "warehouse_area_id" => $warehousearea->id,
            "is_enabled" => 1,
        ]);
    }
}
