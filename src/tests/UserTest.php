<?php
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\User;

class UserTest extends TestCase
{

    use DatabaseTransactions;

    // 注册用户 缺少参数
    public function testRegisterLackParams()
    {

        // Fail
        $this->json(
            "POST",
            "/user/register",
            ["email" => "tangmingming@nle-tech.com"]
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)->seeJsonStructure(
            [
                "status",
                "msg",
                "data" => [
                    "password"
                ]
            ]
        );
    }

    // 注册用户 邮箱格式错误
    public function testRegisterWrongEmail()
    {
        // 错误邮箱
        $this->json(
            "POST",
            "/user/register",
            [
                "email" => "tangmingming@nle",
                "password" => "nle123456,",
                "password_confirmation" => "nle123456,",
            ]
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)->seeJsonStructure(
            [
                "status",
                "msg",
                "data" => [
                    "email"
                ]
            ]
        );
    }

    // 注册用户 email exists
    public function testRegisterEmailExists()
    {
        factory('App\Models\User')->create(
            [
                "email" => "tangmingming@nle-tech.com",
                "password" => '$2y$10$VopUZljMszBLVfEtjEfRve.z2u7MXU7XFdj/6Cys.QJ88EJ8a/c2S',
            ]
        );

        $r = $this->json(
            "POST",
            "/user/register",
            [
                "email" => "tangmingming@nle-tech.com",
                "password" => "nle123456,",
                "password_confirmation" => "nle123456,",
            ]
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)->seeJsonStructure(
            [
                "status",
                "msg",
                "data" => [
                    "email"
                ]
            ]
        );
    }

    // 用户测试 注册成功
    public function testRegister()
    {
        // Success
        $this->json(
            "POST",
            "/user/register",
            [
                "email" => "tangmingming@nle-tech.com",
                "password" => "nle123456,",
                "password_confirmation" => "nle123456,",
            ]
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)->seeJsonStructure(
            [
                "status",
                "msg",
                "data"
            ]
        )->seeInDatabase(
            'user',
            ['email' => 'tangmingming@nle-tech.com']
        );

        $user = User::orderBy("id", "desc")->first();
        $this->seeInDatabase("token", ["owner_user_id" => $user->id, "token_type" => 2]);
    }

    // 测试_用户登录成功
    public function testAuthSucc()
    {
        factory('App\Models\User')->create(
            [
                "nickname" => "zebrapool",
                "name" => "zebrapool123",
                "email" => "tangmingming@nle-tech.com",
                "password" => '$2y$10$VopUZljMszBLVfEtjEfRve.z2u7MXU7XFdj/6Cys.QJ88EJ8a/c2S',
            ]
        );

        $this->json(
            "POST",
            "/user/auth",
            [
                "email" => "tangmingming@nle-tech.com",
                "password" => "nle123456,",
            ]
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)->seeJsonStructure(
            [
                "status",
                "msg",
                "data"
            ]
        );
    }

    // 测试_用户登录失败
    public function testAuthFail()
    {
        factory('App\Models\User')->create(
            [
                "nickname" => "zebrapool",
                "name" => "zebrapool",
                "email" => "tangmingming@nle-tech.com",
                "password" => '$2y$10$VopUZljMszBLVfEtjEfRve.z2u7MXU7XFdj/6Cys.QJ88EJ8a/c2S',
            ]
        );

        $this->json(
            "POST",
            "/user/auth",
            [
                "email" => "tangmingming@nle-tech.com",
                "password" => "123456",
            ]
        )->seeJson(
            [
                "status" => 500
            ]
        )->seeStatusCode(200)->seeJsonStructure(
            [
                "status",
                "msg",
                "data"
            ]
        );
    }

    // 仓库申请认证成功
    public function testOwnerApplySucc()
    {
        $this->fakerUser();

        $this->json(
            "PUT",
            "/owner/apply",
            [
                "warehouse_name_cn" => "测试仓库",
                "warehouse_name_en" => "test warehouse",
                "phone_codes" => "0731",
                "phone" => "12345678901",
                "country" => "cn",
                "postcode" => "410000",
                "door_no" => "123455",
                "city" => "cs",
                "street" => "ttt",
                "warehouse_plan" => "http://a.com/test.jpg",
                "warehouse_property" => "test"
            ]
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)->seeJsonStructure(
            [
                "status",
                "msg",
                "data"
            ]
        )->seeInDatabase(
            'user_certification_owner',
            [
                "warehouse_name_cn" => "测试仓库",
                "warehouse_name_en" => "test warehouse",
                "phone_codes" => "0731",
                "phone" => "12345678901",
                "country" => "cn",
                "postcode" => "410000",
                "door_no" => "123455",
                "city" => "cs",
                "street" => "ttt",
                "warehouse_plan" => "http://a.com/test.jpg",
                "warehouse_property" => "test",
                "status" => 1
            ]
        );
    }

    public function testOwnerApplyFail()
    {
        $this->fakerUser();

        $this->json(
            "PUT",
            "/owner/apply",
            [
                "warehouse_name_cn" => "测试仓库"
            ]
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)->seeJsonStructure(
            [
                "status",
                "msg",
                "data"=>
                    [
                        "warehouse_name_en",
                        "phone_codes",
                        "phone",
                        "country",
                        "postcode",
                        "door_no",
                        "city",
                        "street",
                        //"warehouse_plan",
                        "warehouse_property"
                    ]
            ]
        );
    }
}
