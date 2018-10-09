<?php
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductSpec;
use App\Models\ProductOrigin;

class ProductTest extends TestCase
{

    use DatabaseTransactions;
    /**
     * 商品新增成功
     * @author sw
     */
    public function testCreateProduct()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        factory('App\Models\Category')->create(
            [
                "name_cn" => "测试分类123123123",
                "warning_stock" => 10,
                "parent_id" => 0,
                'is_enabled' => 1,
            ]
        );

        $category = Category::orderBy('id', 'desc')->first();

        $r = $this->json(
            "PUT",
            "/product",
            [
                "category_id" => $category->id,
                "name_cn" => "苹果",
                "name_en" => "apple",
                "storage_compartment" => 19,
                "hs_code" => "ipone7",
                "origin" => "陕西",
                "display_link" => "https://www.baidu.com",
                "remark" => "苹果七个",
                "photos" => "https://www.baidu.com/img/bd_logo1.png",
                "specs" => array(
                    0 => array(
                        "name_cn" => "京东陕西苹果红富士",
                        "name_en" => "jingdongshanxipinguo",
                        "net_weight" => 11,
                        "gross_weight" => 12,
                        "relevance_code" => "IPHONE002"
                    )
                )
            ]
        )->seeJson(
            [
                "status" => 0
            ]
        )
            ->seeStatusCode(200)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            )->seeInDatabase(
                "product",
                [
                    "category_id" => $category->id,
                    "name_cn" => "苹果",
                    "name_en" => "apple",
                    "storage_compartment" => 19,
                    "hs_code" => "ipone7",
                    "origin" => "陕西",
                    "display_link" => "https://www.baidu.com",
                    "remark" => "苹果七个",
                    "photos" => "https://www.baidu.com/img/bd_logo1.png",
                ]
            );
        $product = Product::orderBy("id", "desc")->first();
        $r->seeInDatabase(
            "product_spec",
            [
                "product_id" => $product->id,
                "name_cn" => "京东陕西苹果红富士",
                "name_en" => "jingdongshanxipinguo",
                "net_weight" => 11,
                "gross_weight" => 12,
                "relevance_code" => "IPHONE002"
            ]
        );
    }

    /**
     * 新增商品 参数缺失
     * @author sw
     */
    public function testCreateProductLack()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "PUT",
            "/product",
            [
                "name_cn" => "苹果",
                "name_en" => "apple",
                "storage_compartment" => 19,
                "hs_code" => "ipone7",
                "origin" => "陕西",
                "display_link" => "https://www.baidu.com",
                "remark" => "苹果七个",
                "photos" => "https://www.baidu.com/img/bd_logo1.png",
            ]
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 新增商品 分类不存在
     * @author sw
     */
    public function testCreateProductError()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "PUT",
            "/product",
            [
                "category_id" => 999999,
                "name_cn" => "苹果",
                "name_en" => "apple",
                "storage_compartment" => 19,
                "hs_code" => "ipone7",
                "origin" => "陕西",
                "display_link" => "https://www.baidu.com",
                "remark" => "苹果七个",
                "photos" => "https://www.baidu.com/img/bd_logo1.png",
                "specs" => array(
                    0 => array(
                        "name_cn" => "京东陕西苹果红富士",
                        "name_en" => "jingdongshanxipinguo",
                        "net_weight" => 11,
                        "gross_weight" => 12,
                        "relevance_code" => "IPHONE002"
                    )
                )
            ]
        )->seeJson(
            [
                "status" => 404
            ]
        )->seeStatusCode(200)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 修改商品信息成功
     * @author sw
     */
    public function testUpdateProduct()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        factory('App\Models\Category')->create(
            [
                "name_cn" => "测试分类01iphone",
                "warning_stock" => 10,
                "parent_id" => 0
            ]
        );
        $category = Category::orderBy('id', 'desc')->first();
        factory('App\Models\Product')->create(
            [
                'name_en' => "apple",
                'category_id' => $category->id,
                'hs_code' => "ipone7",
                'storage_compartment' => 19,
                'origin' => "陕西",
                'display_link' => "https://www.baidu.com",
                'remark' => "苹果七个",
                'photos' => "https://www.baidu.com/img/bd_logo1.png",
                "owner_id" => $user->id,//用户id
            ]
        );
        $product = Product::orderBy('id', 'desc')->first();
        $this->json(
            "POST",
            "/product",
            [
                "product_id" => $product->id,
                "category_id" => $category->id,
                'name_cn' => "苹果2",
                'name_en' => "apple2",
                'hs_code' => "ipone7",
                'storage_compartment' => 20,
                'origin' => "陕西",
                'display_link' => "https://www.baidu.com",
                'remark' => "苹果七个",
                'photos' => "https://www.baidu.com/img/bd_logo1.png",
                "owner_id" => $user->id,
            ]
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            )->seeInDatabase(
                "product",
                [
                    "id" => $product->id,
                    "category_id" => $category->id,
                    'name_cn' => "苹果2",
                    'name_en' => "apple2",
                    'hs_code' => "ipone7",
                    'storage_compartment' => 20,
                    'origin' => "陕西",
                    'display_link' => "https://www.baidu.com",
                    'remark' => "苹果七个",
                    'photos' => "https://www.baidu.com/img/bd_logo1.png",
                    "owner_id" => $user->id,
                ]
            );
    }

    /**
     * 修改商品 缺少参数
     * @author sw
     */
    public function testUpdateProductLack()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "POST",
            "/product",
            [
                "product_id" => 1
            ]
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data",
                ]
            );
    }

    /**
     * 修改商品 货品不存在
     */
    public function testUpdateProductNoexistProduct()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        factory('App\Models\Category')->create(
            [
                "name_cn" => "测试分类123123123",
                "warning_stock" => 10,
                "parent_id" => 0
            ]
        );
        $category = Category::orderBy('id', 'desc')->first();
        $this->json(
            "POST",
            "/product",
            [
                "product_id" => 1,
                "category_id" => $category->id,
                'name_cn' => "苹果",
                'name_en' => "apple",
                'hs_code' => "ipone7",
                'storage_compartment' => 20,
                'origin' => "陕西",
                'display_link' => "https://www.baidu.com",
                'remark' => "苹果七个",
                'photos' => "https://www.baidu.com/img/bd_logo1.png",
                "owner_id" => $user->id,
            ]
        )->seeJson(
            [
                'status' => 404
            ]
        )->seeStatusCode(404)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 修改商品 商品分类不存在
     */
    public function testUpdateProductNoexistCategory()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        factory('App\Models\Product')->create(
            [
                'name_en' => "apple",
                'category_id' => 999,
                'hs_code' => "ipone7",
                'storage_compartment' => 19,
                'origin' => "陕西",
                'display_link' => "https://www.baidu.com",
                'remark' => "苹果七个",
                'photos' => "https://www.baidu.com/img/bd_logo1.png",
                "owner_id" => $user->id,//用户id
            ]
        );
        $product = Product::orderBy('id', 'desc')->first();
        $this->json(
            "POST",
            "/product",
            [
                "product_id" => $product->id,
                "category_id" => 99999999,
                'name_cn' => "苹果",
                'name_en' => "apple",
                'hs_code' => "ipone7",
                'storage_compartment' => 20,
                'origin' => "陕西",
                'display_link' => "https://www.baidu.com",
                'remark' => "苹果七个",
                'photos' => "https://www.baidu.com/img/bd_logo1.png",
                "owner_id" => $user->id,
            ]
        )->seeJson(
            [
                'status' => 404
            ]
        )->seeStatusCode(404)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 获取商品详情成功
     * @author sw
     */
    public function testProductDetail()
    {
        $user = $this->fakerUser();
        factory("App\Models\Product")->create(
            [
                "name_en" => "unitProducttest",//商品名称
                "category_id" => 1,//商品分类
                "hs_code" => 1,//海关编码
                "storage_compartment" => 1,//储存温度
                "origin" => 1,//箱子条码信息
                "display_link" => "www.baidu.com",//商品链接
                "remark" => "测试商品",//商品备注
                "photos" => "red.jpg",//商品图片
                "owner_id" => $user->id,//用户id
            ]
        );
        $product = Product::orderBy("id", "desc")->first();
        $this->json(
            "get",
            "/product/{$product->id}",
            []
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)
            ->seeJsonContains(
                [
                    "data" =>
                        [
                            "name_en" => "unitProducttest",//商品名称
                            "category_id" => 1,//商品分类
                            "hs_code" => 1,//海关编码
                            "storage_compartment" => 1,//储存温度
                            "origin" => 1,//箱子条码信息
                            "display_link" => "www.baidu.com",//商品链接
                            "remark" => "测试商品",//商品备注
                            "photos" => "red.jpg",//商品图片
                            "owner_id" => $user->id,//用户id
                        ]
                ],
                true
            )
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 新增货品规格成功
     * @author sw
     */
    public function testCreateProductSpec()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        factory("App\Models\Product")->create(
            [
                "name_en" => "unitProducttest",//商品名称
                "category_id" => 1,//商品分类
                "hs_code" => 1,//海关编码
                "storage_compartment" => 1,//储存温度
                "origin" => 1,//箱子条码信息
                "display_link" => "www.baidu.com",//商品链接
                "remark" => "测试商品",//商品备注
                "photos" => "red.jpg",//商品图片
                "owner_id" => $user->id,//用户id
            ]
        );
        $product = Product::orderBy("id", "desc")->first();
        $this->json(
            "PUT",
            "/spec",
            [
                "product_id" => $product->id,
                "name_cn" => "测试产品规格001",
                "name_en" => "testProductSpec001",
                "net_weight" => 0.00,
                "gross_weight" => 0.00,
                "relevance_code" => "macodexiongshi",
                "owner_id" => $user->id,//用户id
            ]
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            )->seeInDatabase(
                "product_spec",
                [
                    "product_id" => $product->id,
                    "name_cn" => "测试产品规格001",
                    "name_en" => "testProductSpec001",
                    "net_weight" => 0.00,
                    "gross_weight" => 0.00,
                    "relevance_code" => "macodexiongshi",
                    "owner_id" => $user->id,//用户id
                ]
            );
    }

    /**
     * 新增货品规则  参数产品id缺失
     * @author sw
     */
    public function testCreateProductSpecNoProductid()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        $this->json(
            "PUT",
            "/spec",
            [
                "product_id" => "",
                "name_cn" => "测试产品规格001",
                "name_en" => "testProductSpec001",
                "net_weight" => 0.00,
                "gross_weight" => 0.00,
                "relevance_code" => "macode",
                "owner_id" => $user->id,//用户id
            ]
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 新增货品规则  货品不存在
     * @author sw
     */
    public function testCreateProductSpecNoExistProductid()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        $this->json(
            "PUT",
            "/spec",
            [
                "product_id" => 99999,
                "name_cn" => "测试产品规格001",
                "name_en" => "testProductSpec001",
                "net_weight" => 0.00,
                "gross_weight" => 0.00,
                "relevance_code" => "macodexiongshi",
                "owner_id" => $user->id,//用户id
            ]
        )->seeJson(
            [
                "status" => 404
            ]
        )->seeStatusCode(404)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 修改货品规则成功
     * @author sw
     */
    public function testUpdateProductSpec()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        factory("App\Models\Product")->create(
            [
                "name_en" => "unitProducttest",//商品名称
                "category_id" => 1,//商品分类
                "hs_code" => 1,//海关编码
                "storage_compartment" => 1,//储存温度
                "origin" => 1,//箱子条码信息
                "display_link" => "www.baidu.com",//商品链接
                "remark" => "测试商品",//商品备注
                "photos" => "red.jpg",//商品图片
                "owner_id" => $user->id,//用户id
            ]
        );
        $product = Product::orderBy("id", "desc")->first();
        factory("App\Models\ProductSpec")->create(
            [
                "product_id" => $product->id,//供货商编号
                "name_cn" => "测试修改货品规则001",//商品名称
                "name_en" => "testUpdateProductFormat001",//商品名称
                "net_weight" => 2.00,//已入库数量
                "gross_weight" => 2.00,//每件箱数
                "relevance_code" => "testmacode002",//商品备注
                "owner_id" => $user->id,//用户id
            ]
        );
        $product_spec = ProductSpec::orderBy("id", "desc")->first();
        $this->json(
            "POST",
            "/spec",
            [
                "spec_id" => $product_spec->id,
                "name_cn" => "测试修改货品规则002",
                "name_en" => "testUpdateProductFormat002",
                "net_weight" => 3.00,
                "gross_weight" => 3.00,
                "relevance_code" => "testmacode003",
                "owner_id" => $user->id
            ]
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            )->seeInDatabase(
                "product_spec",
                [
                    "id" => $product_spec->id,
                    "name_cn" => "测试修改货品规则002",
                    "name_en" => "testUpdateProductFormat002",
                    "net_weight" => 3.00,
                    "gross_weight" => 3.00,
                    "relevance_code" => "testmacode003",
                    "owner_id" => $user->id
                ]
            );
    }

    /**
     * 修改货品规则 货品规则id不存在
     * @author sw
     */
    public function testUpdateProductSpecNoExistSpecid()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        $this->json(
            "POST",
            "/spec",
            [
                "spec_id" => 999999,
                "name_cn" => "测试修改货品规则002",
                "name_en" => "testUpdateProductFormat002",
                "net_weight" => 3.00,
                "gross_weight" => 3.00,
                "relevance_code" => "testmacode003",
                "owner_id" => $user->id
            ]
        )->seeJson(
            [
                "status" => 404
            ]
        )->seeStatusCode(404)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 修改商品规则 参数relevance_code已存在
     * @author sw
     */
    public function testUpdateProductSpecExistRelevanceCode()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        factory("App\Models\Product")->create(
            [
                "name_en" => "unitProducttest",//商品名称
                "category_id" => 1,//商品分类
                "hs_code" => 1,//海关编码
                "storage_compartment" => 1,//储存温度
                "origin" => 1,//箱子条码信息
                "display_link" => "www.baidu.com",//商品链接
                "remark" => "测试商品",//商品备注
                "photos" => "red.jpg",//商品图片
                "owner_id" => $user->id,//用户id
            ]
        );
        $product = Product::orderBy("id", "desc")->first();
        factory("App\Models\ProductSpec")->create(
            [
                "product_id" => $product->id,//供货商编号
                "name_cn" => "测试修改货品规则001",//商品名称
                "name_en" => "testUpdateProductFormat001",//商品名称
                "net_weight" => 2.00,//已入库数量
                "gross_weight" => 2.00,//每件箱数
                "relevance_code" => "testmacode002",//商品备注
                "owner_id" => $user->id,//用户id
            ]
        );
        factory("App\Models\ProductSpec")->create(
            [
                "product_id" => $product->id,//供货商编号
                "name_cn" => "测试修改货品规则001",//商品名称
                "name_en" => "testUpdateProductFormat001",//商品名称
                "net_weight" => 2.00,//已入库数量
                "gross_weight" => 2.00,//每件箱数
                "relevance_code" => "testmacode003",//商品备注
                "owner_id" => $user->id,//用户id
            ]
        );
        $product_spec = ProductSpec::orderBy("id", "desc")->first();
        $this->json(
            "POST",
            "/spec",
            [
                "spec_id" => $product_spec->id,
                "name_cn" => "测试修改货品规则002",
                "name_en" => "testUpdateProductFormat002",
                "net_weight" => 3.00,
                "gross_weight" => 3.00,
                "relevance_code" => "testmacode002",
                "owner_id" => $user->id
            ]
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     *货品规则删除成功
     * @author sw
     */
    public function testDeleteProductSpec()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        factory("App\Models\Product")->create(
            [
                "name_en" => "unitProducttest",//商品名称
                "category_id" => 1,//商品分类
                "hs_code" => 1,//海关编码
                "storage_compartment" => 1,//储存温度
                "origin" => 1,//箱子条码信息
                "display_link" => "www.baidu.com",//商品链接
                "remark" => "测试商品",//商品备注
                "photos" => "red.jpg",//商品图片
                "owner_id" => $user->id,//用户id
            ]
        );
        $product = Product::orderBy("id", "desc")->first();
        factory("App\Models\ProductSpec")->create(
            [
                "product_id" => $product->id,//供货商编号
                "name_cn" => "测试修改货品规则001",//商品名称
                "name_en" => "testUpdateProductFormat001",//商品名称
                "net_weight" => 2.00,//已入库数量
                "gross_weight" => 2.00,//每件箱数
                "relevance_code" => "testmacode003",//商品备注
                "owner_id" => $user->id,//用户id
            ]
        );
        $product_spec = ProductSpec::orderBy("id", "desc")->first();
        $this->json(
            "DELETE",
            "/spec",
            ["spec_id" => $product_spec->id]
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            )->notSeeInDatabase(
                "product_spec",
                [
                    "product_id" => $product->id,//供货商编号
                    "name_cn" => "测试修改货品规则001",//商品名称
                    "name_en" => "testUpdateProductFormat001",//商品名称
                    "net_weight" => 2.00,//已入库数量
                    "gross_weight" => 2.00,//每件箱数
                    "relevance_code" => "testmacode001",//商品备注
                    "owner_id" => $user->id,//用户id
                ]
            );
    }

    /**
     * 货品删除失败 货品id不存在
     * @author sw
     */
    public function testDeleteProductSpecNoExistProductSpec()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "DELETE",
            "/spec",
            ["spec_id" => 999999]
        )->seeJson(
            [
                "status" => 404
            ]
        )->seeStatusCode(404)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 新增产地成功
     * @author sw
     */
    public function testCreateOrigin()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "PUT",
            "/origin",
            ["name_cn" => "测试新增产地01"]
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            )->seeInDatabase(
                "product_origin",
                [
                    "name_cn" => "测试新增产地01"
                ]
            );
    }

    /**
     * 新增产地 name_cn缺失
     * @author sw
     */
    public function testCreateOriginNoNamecn()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "PUT",
            "/origin",
            []
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 新增产地 name_cn已存在
     * @author sw
     */
    public function testCreateOriginAlreadyExists()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        factory("App\Models\ProductOrigin")->create(
            [
                "name_cn" => "测试新增产地001",//商品名称
                "name_en" => "testCreateOrigin001",//商品名称
                "user_id" => $user->id,
            ]
        );
        $this->json(
            "PUT",
            "/origin",
            ["name_cn" => "测试新增产地001"]
        )->seeJson(
            [
                "status" => 1
            ]
        )->seeStatusCode(200)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 修改产地成功
     * @author sw
     */
    public function testUpdateOrigin()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        factory("App\Models\ProductOrigin")->create(
            [
                "name_cn" => "测试修改产地001",//商品名称
                "name_en" => "testUpdateOrigin001",//商品名称
                "user_id" => $user->id,
            ]
        );
        $product_origin = ProductOrigin::orderBy("id", "desc")->first();
        $this->json(
            "POST",
            "/origin",
            [
                "id" => $product_origin->id,
                "name_cn" => "测试修改产地002",
                "name_en" => "testUpdateOrigin001",//商品名称
                "user_id" => $user->id
            ]
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            )->seeInDatabase(
                "product_origin",
                [
                    "id" => $product_origin->id,
                    "name_cn" => "测试修改产地002",
                    "name_en" => "testUpdateOrigin001",//商品名称
                    "user_id" => $user->id
                ]
            );
    }

    /**
     * 修改产地 参数缺失
     * @author sw
     */
    public function testUpdateOriginLack()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "POST",
            "/origin",
            []
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 修改产地 id不存在
     * @author sw
     */
    public function testUpdateOriginNoExistsId()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "POST",
            "/origin",
            [
                "id" => 99999,
                "name_cn" => "测试修改产地001"
            ]
        )->seeJson(
            [
                "status" => 404,
            ]
        )->seeStatusCode(404)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 修改产地 产地名重复
     * @author sw
     */
    public function testUpdateOriginExistsNamecn()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        factory("App\Models\ProductOrigin")->create(
            [
                "name_cn" => "测试修改产地001",//商品名称
                "name_en" => "testUpdateOrigin001",//商品名称
                "user_id" => $user->id,
            ]
        );
        factory("App\Models\ProductOrigin")->create(
            [
                "name_cn" => "测试修改产地002",//商品名称
                "name_en" => "testUpdateOrigin002",//商品名称
                "user_id" => $user->id,
            ]
        );
        $origin = ProductOrigin::orderBy("id", "desc")->first();
        $this->json(
            "POST",
            "/origin",
            [
                "id" => $origin->id,
                "name_cn" => "测试修改产地001",
                "name_en" => "testUpdateOrigin002",//商品名称
                "user_id" => $user->id,
            ]
        )->seeJson(
            [
                "status" => 1
            ]
        )->seeStatusCode(200)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 删除产地成功
     * @author sw
     */
    public function testDeleteOrigin()
    {
        $this->withoutMiddleware();
        $user = $this->fakerUser();
        factory("App\Models\ProductOrigin")->create(
            [
                "name_cn" => "测试删除产地001",//商品名称
                "name_en" => "testDeleteOrigin001",//商品名称
                "user_id" => $user->id,
            ]
        );
        $origin = ProductOrigin::orderBy("id", "desc")->first();
        $this->json(
            "DELETE",
            "/origin",
            [
                "id" => $origin->id,
            ]
        )->seeJson(
            [
                "status" => 0
            ]
        )->seeStatusCode(200)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            )->notSeeInDatabase(
                "product_origin",
                [
                    "name_cn" => "测试删除产地001",//商品名称
                    "name_en" => "testDeleteOrigin001",//商品名称
                    "user_id" => $user->id,
                ]
            );
    }

    /**
     * 删除产地 产地id不存在
     * @author sw
     */
    public function testDeleteOriginNoExistsId()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "DELETE",
            "/origin",
            ["id" => ""]
        )->seeJson(
            [
                "status" => 404
            ]
        )->seeStatusCode(404)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 测试获取出入库记录 参数spec_id缺失
     * @author sw
     */
    public function testRecordStorageAndOutboundLack()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "GET",
            "/stock/log",
            []
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 测试获取出入库记录 参数spec_id不为整形
     * @author sw
     */
    public function testRecordStorageAndOutboundString()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "GET",
            "/stock/log",
            [
                "spec_id" => "ascdddd"
            ]
        )->seeJson(
            [
                "status" => 422
            ]
        )->seeStatusCode(422)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }

    /**
     * 测试获取出入库记录 货品规格不存在  目前接口没做这类校验 是否需要？
     * @author sw
     */
//    public function testRecordStorageAndOutboundNoExistSpecid()
//    {
//        $this->withoutMiddleware();
//        $this->fakerUser();
//        $this->json(
//            "GET",
//            "/stock/log",
//            [
//                "spec_id" => 9999
//            ]
//        )->seeJson(
//            [
//                "status" => 1
//            ]
//        )->seeStatusCode(200)
//            ->seeJsonStructure(
//                [
//                    "status",
//                    "msg",
//                    "data"
//                ]
//            );
//    }

    /**
     * 测试获取出入库记录成功
     * @notice 返回无效的json
     * @author sw
     */
//    public function testRecordStorageAndOutbound()
//    {
//        $this->withoutMiddleware();
//        $this->fakerUser();
//        $this->json(
//            "GET",
//            "/stock/log",
//            [
//                "spec_id" => 99999,
//            ]
//        )->seeJson(
//            [
//                "status" => 500
//            ]
//        )->seeStatusCode(200)
//            ->seeJsonStructure(
//                [
//                    "status",
//                    "msg",
//                    "data"
//                ]
//            );
//    }

    /**
     * 测试保存商品库存报警信息 返回无效的json
     * @author sw
     */
//    public function testSaveStockStore()
//    {
//        $this->withoutMiddleware();
//        $this->fakerUser();
//        factory('App\Models\Category')->create(
//            [
//                "name_cn" => "测试保存商品分类001",
//                "warning_stock" => 10,
//                "parent_id" => 0
//            ]
//        );
//        $category = Category::orderBy('id', 'desc')->first();
//        $this->json(
//            "POST",
//            "/warning/stock",
//            [
//                "default_warning_stock" => 100,
//                "warning_email" => "2557929467@qq.com",
//                "warning_data"=>array(
//                    0=>array(
//                        "category_id"=>$category->id,
//                        "warning_stock"=>11,
//                    )
//                )
//            ]
//        )->seeJson(
//            [
//                "status" => 0
//            ]
//        )->seeStatusCode(200)
//            ->seeJsonStructure(
//                [
//                    "status",
//                    "msg",
//                    "data"
//                ]
//            );
//    }

    /**
     * 测试获取分类列表
     * @author sw
     */
    public function testObtainCategory()
    {
        $this->withoutMiddleware();
        $this->fakerUser();
        $this->json(
            "GET",
            "/category",
            []
        )->seeJson(
            [
                "status"=>0
            ]
        )->seeStatusCode(200)
            ->seeJsonStructure(
                [
                    "status",
                    "msg",
                    "data"
                ]
            );
    }
}
