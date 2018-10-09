<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\User;
use App\Models\Warehouse;

class WarehouseTest extends TestCase
{

    use DatabaseTransactions;

#    function testGetToken() {
#        factory('App\Models\User')->create(
#            [
#                "nickname" => "zebrapool",
#                "name" => "zebrapool",
#                "email" => "tangmingming@nle-tech.com",
#                "password" => '$2y$10$VopUZljMszBLVfEtjEfRve.z2u7MXU7XFdj/6Cys.QJ88EJ8a/c2S',
#            ]
#        );
#
#        $this->json(
#            "POST",
#            "/user/auth",
#            [
#                "email" => "tangmingming@nle-tech.com",
#                "password" => "nle123456,",
#            ]
#        );
#        $token = Token::first();
#        return $token["token_value"];
#    }

    // 创建仓库 缺少参数
    public function testCreateWarehouseLackParams()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "PUT",
            "/warehouse",
            ["name_cn" => "测试仓库"]
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data" => [
                        "name_en",
                        "type",
                        "code",
                        "country",
                        "city",
                        "street",
                        "temperature",
                        "area",
                        "contact_user",
                        "contact_number",
                        "contact_email"
                    ]
                ]
            );
    }

    //创建仓库成功
    // public function testCreateWarehouseSucc()
    // {
    //     $this->withoutMiddleware();
    //     $user = $this->fakerUser();

    //     factory('App\Models\UserExtra')->create([
    //         'user_id' => $user->id,
    //         'is_certificated_creator' => 1,
    //         'is_certificated_renter' => 1,
    //         'self_use_limit' => 10,
    //         'share_limit' => 10,
    //     ]);

    //     $r = $this->json(
    //         "PUT",
    //         "/warehouse",
    //         [
    //             "name_cn" => "测试仓库",
    //             "name_en" => "warehouse_1",
    //             "type" => 1,
    //             "code" => "abcd_01",
    //             "country" => "cn",
    //             "city" => "changsha",
    //             "street" => "some road",
    //             "postcode" => "410000",
    //             'door_no' => '1',
    //             "status" => TRUE,
    //             "temperature" => 1,
    //             "area" => "2500",
    //             "contact_user" => "some people",
    //             "contact_number" => "12345678911",
    //             "contact_email" => "test@nle-tech.com"
    //         ]
    //     )->seeStatusCode(200)->seeJson(
    //         [
    //             "status" => 0
    //         ]
    //     )->seeJsonStructure(
    //         [
    //             "status",
    //             "msg",
    //             "data" => [
    //             ]
    //         ]
    //     );

    //     $r->seeInDatabase(
    //         'warehouse', [
    //             "name_cn" => "测试仓库",
    //             "name_en" => "warehouse_1",
    //             "type" => 1,
    //             "code" => "abcd_01",
    //             "country" => "cn",
    //             "city" => "changsha",
    //             "street" => "some road",
    //             "postcode" => "410000abc",
    //             'door_no' => '1',
    //             "temperature" => 1,
    //             "area" => 2500,
    //             "contact_user" => "some people",
    //             "contact_number" => "12345678911",
    //             "contact_email" => "test@nle-tech.com",
    //         ]
    //     );
    // }

    //更新仓库成功
    // public function testUpdateWarehouseSucc()
    // {
    //     $this->withoutMiddleware();
    //     factory('App\Models\Warehouse')->create(
    //         [
    //             "name_cn" => "测试仓库",
    //             "name_en" => "warehouse_1",
    //             "type" => 1,
    //             "code" => "abcd_01",
    //             "country" => "cn",
    //             "city" => "changsha",
    //             "street" => "some road",
    //             "postcode" => "410000",
    //             'door_no' => '1',
    //             "status" => 1,
    //             "temperature" => 1,
    //             "area" => "2500",
    //             "contact_user" => "some people",
    //             "contact_number" => "12345678911",
    //             "contact_email" => "test@nle-tech.com"
    //         ]
    //     );
    //     $warehouse = Warehouse::orderBy("id", "desc")->first();

    //     $this->fakerUser();
    //     $this->json(
    //         "POST",
    //         "/warehouse", [
    //             "name_cn" => "测试仓库",
    //             "name_en" => "warehouse_2",
    //             "type" => 1,
    //             "code" => "abcd_01",
    //             "country" => "cn",
    //             "city" => "shanghai",
    //             "street" => "some road",
    //             "postcode" => "410000abc",
    //             'door_no' => '1',
    //             "status" => 1,
    //             "temperature" => 1,
    //             "area" => "2500",
    //             "contact_user" => "some people",
    //             "contact_number" => "12345678911",
    //             "contact_email" => "test@nle-tech.com",
    //             "warehouse_id" => $warehouse->id
    //         ]
    //     )->seeStatusCode(200)->seeJson(
    //         [
    //             "status" => 0
    //         ]
    //     )->seeJsonStructure(
    //         [
    //             "status",
    //             "msg",
    //             "data" => [
    //             ]
    //         ]
    //     )->seeInDatabase(
    //         'warehouse', [
    //             "name_cn" => "测试仓库",
    //             "name_en" => "warehouse_2",
    //             "type" => 1,
    //             "code" => "abcd_01",
    //             "country" => "cn",
    //             "city" => "shanghai",
    //             "street" => "some road",
    //             "postcode" => "410000abc",
    //             "status" => 1,
    //             "temperature" => 1,
    //             "area" => "2500",
    //             "contact_user" => "some people",
    //             "contact_number" => "12345678911",
    //             "contact_email" => "test@nle-tech.com",
    //             "id" => $warehouse->id
    //         ]
    //     );
    // }

    // 仓库详情
    public function testGetWarehouseSucc()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
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
                "temperature" => 1,
                "area" => "2500",
                "contact_user" => "some people",
                "contact_number" => "12345678911",
                "contact_email" => "test@nle-tech.com"
            ]
        );
        $warehouse = Warehouse::orderBy("id", "desc")->first();
        $this->json(
            "GET",
            "/warehouse/{$warehouse->id}",
            []
        )->seeJsonContains(
            [
                "data" =>
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
                        "temperature" => 1,
                        "area" => 2500,
                        "contact_user" => "some people",
                        "contact_number" => "12345678911",
                        "contact_email" => "test@nle-tech.com"
                    ]
            ],
            true
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)->seeJsonStructure(
            [
                "status",
                "msg",
                "data" => [
                ]
            ]
        );
    }
}
