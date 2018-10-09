<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->userName,
        'email' => $faker->email,
        'nickname' => $faker->name,
        'password' => $faker->password,
    ];
});

$factory->define(App\Models\UserExtra::class, function (Faker\Generator $faker) {
    return [
        'user_id' => $faker->numberBetween(1, 20),
        'is_certificated_creator' => $faker->boolean,
        'is_certificated_renter' => $faker->boolean,
        'self_use_limit' => $faker->numberBetween(1, 20),
        'share_limit' => $faker->numberBetween(1, 20),
    ];
});

$factory->define(App\Models\Warehouse::class, function (Faker\Generator $faker) {
    return [
        'name_cn' => $faker->name,
        'name_en' => $faker->name,
        'code' => $faker->regexify('[A-Za-z0-9_]{1,12}'),
        'type' => $faker->numberBetween(1,2),
        'temperature' => $faker->numberBetween(1,3),
        'area' => $faker->numberBetween(1,99999),
        'country' => $faker->country,
        'city' => $faker->city,
        'street' => $faker->streetAddress,
        'contact_user' => $faker->name,
        'contact_number' => $faker->phoneNumber,
        'contact_email' => $faker->freeEmail,
        'status' => $faker->numberBetween(1,2),
        'logo_photo' => $faker->imageUrl()
    ];
});

$factory->define(App\Models\WarehouseArea::class, function (Faker\Generator $faker) {
    return [
        'code'        => $faker->regexify('[A-Za-z0-9_]{1,12}'),
        'name_cn'     => $faker->name,
        'name_en'     => $faker->name,
        'warehouse_id'=> $faker->numberBetween(1, 11),
        'temperature' => $faker->numberBetween(1, 3),
        'is_enabled'  => $faker->boolean,
        'functions'   => $faker->text(255),
        'remark'      => $faker->text(255),
    ];
});

$factory->define(App\Models\Batch::class, function (Faker\Generator $faker) {
    return [
        'id'                => $faker->numberBetween(1,11),
        'batch_code'        => $faker->text(255),
        'plan_time'         => $faker->unixTime,
        'over_time'         => $faker->unixTime,
        'type_id'           => $faker->numberBetween(1,11),
        'distributor_id'    => $faker->numberBetween(1,11),
        'warehouse_id'      => $faker->numberBetween(1,11),
        'operator'          => $faker->numberBetween(1,11),
        'num'               => $faker->numberBetween(1,11),
        'need_num'          => $faker->numberBetween(1,11),
        'remarks'           => $faker->text(255),
        'owner_id'          => $faker->numberBetween(1,11),
        'status'            => $faker->numberBetween(1,3),
    ];
});
//商品factory
$factory->define(App\Models\Product::class,function (Faker\Generator $faker) {
    return [
        'name_en'             => $faker->name,        //商品名称
        'category_id'         => $faker->numberBetween(1,11),                           //分类
        'hs_code'             => $faker->numberBetween(1,11),                           //海关编码
        'storage_compartment' => $faker->numberBetween(1,11),       //储存温度
        'origin'              => $faker->numberBetween(1,11),        //箱子条码信息
        'display_link'        => $faker->numberBetween(1,11),        //商品链接
        'remark'              => $faker->numberBetween(1,11),                           //备注
        'photos'              => $faker->imageUrl(),                           //商品图片
    ];
});
//商品规格
$factory->define(App\Models\ProductSpec::class,function (Faker\Generator $faker) {
    return [
        'product_id'     => $faker->numberBetween(1,11),                     //供货商编号
        'name_cn'      => $faker->name,
        'name_cn'      => $faker->name,
        'net_weight'     => $faker->randomFloat(),                           //已入库数量
        'gross_weight'   => $faker->randomFloat(),                           //每件箱数
        'relevance_code' => $faker->numberBetween(1,11), //备注
    ];
});
//商品库存
$factory->define(App\Models\ProductStock::class,function (Faker\Generator $faker) {
    return [
        'batch_id' => $faker->numberBetween(1,11),       //供货商编号
        'stockin_num'=> $faker->numberBetween(1,11),                           //已入库数量
        'pieces_num' => $faker->numberBetween(1,11),                           //每件箱数
        'remark'    => $faker->text(255),       //备注
        'box_code'   => $faker->text(255),       //箱子条码信息
        'ean'        => $faker->text(255),       //ena编码
        'expiration_date' => $faker->numberBetween(1,11),                      //商品保质期
        'production_batch_number' => $faker->numberBetween(1,11),              //生产批次号
    ];
});
//商品分类
$factory->define(App\Models\Category::class, function (Faker\Generator $faker) {
    return [
        'name_cn' => $faker->name,
        'name_en' =>$faker->name,
        'is_enabled' => $faker->boolean,
        'need_production_batch_number' => $faker->boolean,
        'need_expiration_date' => $faker->boolean
    ];
});
//商品产地库
$factory->define(App\Models\ProductOrigin::class, function (Faker\Generator $faker) {
    return [
        'name_cn' => $faker->name,//分类名称
        'name_en' => $faker->name,//分类名称
    ];
});

//货架
$factory->define(App\Models\Shelf::class, function (Faker\Generator $faker) {
    return [
        'code'        => $faker->regexify('[A-Za-z0-9_]{1,12}'),
        'warehouse_id'=> $faker->numberBetween(1, 11),
        'warehouse_area_id'=> $faker->numberBetween(1, 11),
        'capacity'=> $faker->numberBetween(1, 11),
        'passage'     => $faker->name,
        'row'     => $faker->name,
        'col'     => $faker->name,
        'floor'     => $faker->name,
        'is_enabled' => $faker->boolean,
        'remark'      => $faker->text(255)
    ];
});

//托盘
$factory->define(App\Models\Tray::class, function (Faker\Generator $faker) {
    return [
        'code'        => $faker->regexify('[A-Za-z0-9_]{1,12}'),
        'shelf_id'=> $faker->numberBetween(1, 11),
        'warehouse_id'=> $faker->numberBetween(1, 11),
        'plies'     => $faker->name,
        'place'     => $faker->name,
        'status' => $faker->boolean,
        'capacity'=> $faker->numberBetween(1, 11),
        'weight'=> $faker->numberBetween(1, 11),
        'is_enabled' => $faker->boolean
    ];
});

//OrderType出库单分类
$factory->define(App\Models\OrderType::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text(255),
        'warehouse_id' => $faker->numberBetween(1, 11),
        'area_id' => $faker->numberBetween(1, 11),
        'is_enabled' => $faker->boolean,
        'is_partial' => $faker->boolean
        ];
});
//Order出库单
$factory->define(App\Models\Order::class, function (Faker\Generator $faker) {
    return [
        'out_sn' => $faker->text(255),
        'source' => $faker->text(32),
        'operator' => $faker->numberBetween(1, 3),
        'status' => $faker->numberBetween(1, 3),
        'delivery_date' => $faker->unixTime,
        'delivery_type' => $faker->numberBetween(1, 3),
        'receiver_country' => $faker->text(255),
        'receiver_city' => $faker->text(255),
        'receiver_postcode' => $faker->text(255),
        'receiver_doorno' => $faker->text(64),
        'receiver_address' => $faker->text(64),
        'receiver_fullname' => $faker->name,
        'receiver_phone' => $faker->numberBetween(1, 11),
        'invoice_number' => $faker->numberBetween(1, 3),
        'vip_id' => $faker->numberBetween(1, 11),
        'owner_id' => $faker->numberBetween(1, 11),
        'send_country' => $faker->text(255),
        'send_city' => $faker->text(255),
        'send_postcode' => $faker->text(255),
        'send_address' => $faker->text(64),
        'send_fullname' => $faker->text(64),
        'send_phone' => $faker->text(32),
        'receiver_province' => $faker->text(64),
        'express_num' => $faker->numberBetween(1, 11),
        'warehouse_id' => $faker->numberBetween(1, 11),
        'shipment_num' => $faker->numberBetween(1, 11),
        'is_tobacco' => $faker->boolean,
        'order_type' => $faker->numberBetween(1, 3)
    ];
});
