<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return  "upgrade success!".env('APP_ENV');
});

// 用户认证
Route::post('/login', 'AuthController@login');
Route::post('/logout', 'AuthController@logout');
Route::post('/register', 'UserController@register');
Route::post('/code', 'UserController@getCode');
Route::post('/callUser', 'UserController@callUser');

Route::post('/user/forgetPassword', 'PasswordController@store');// 忘记密码-请求重置
Route::get('/user/resetPassword/{token_value}', [
    'as' => 'pwd-activation',
    'uses' => 'PasswordController@show',
]);// 忘记密码-重置密码链接
Route::post('/user/resetPassword', 'PasswordController@edit');// 忘记密码-重置密码接口

Route::middleware(['auth:jwt'])->group(function () {
    Route::get('/home/notice', 'HomePageController@notice');// 首页通知
    Route::get('/home/analyze', 'HomePageController@analyze');// 首页仓库
    Route::get('/home/analyzeTable', 'HomePageController@batchOrOrderCount');// 首页仓库


    Route::post('/user/{user_id}/password', 'UserController@resetPassword');// 修改密码
    Route::get('/user/{user_id}/privilege', 'UserController@privilege');//获取员工权限
    Route::post('/user/{user_id}/info', 'UserController@updateInfo');//修改员工个人资料
    Route::post('/user/{user_id}/avatar', 'UserController@avatar');//修改员工头像
    Route::get('/user/{user_id}', 'UserController@show');//获取员工权限

    //上传图片
    Route::post('/upload/image', 'UploadController@image');

    //仓库
    Route::get('/warehouses', 'WarehouseController@index');
    Route::post('/warehouses', 'WarehouseController@store');
    Route::put('/warehouses/{warehouse_id}', 'WarehouseController@update');
    Route::delete('/warehouses/{warehouse_id}', 'WarehouseController@destroy');
    Route::post('/warehouses/default/{warehouse_id}', 'WarehouseController@setDefault');

    //商品分类
    Route::get('/categories', 'CategoryController@index');
    Route::post('/categories', 'CategoryController@store');
    Route::put('/categories/{category_id}', 'CategoryController@update');
    Route::delete('/categories/{category_id}', 'CategoryController@destroy');

    //仓库特性
    Route::get('/features', 'WarehouseFeatureController@index');
    Route::post('/features', 'WarehouseFeatureController@store');
    Route::put('/features/{feature_id}', 'WarehouseFeatureController@update');
    Route::delete('/features/{feature_id}', 'WarehouseFeatureController@destroy');


    //仓库货位
    Route::get('/locations', 'WarehouseLocationController@index');
    Route::post('/locations', 'WarehouseLocationController@store');
    Route::put('/locations/{location_id}', 'WarehouseLocationController@update');
    Route::delete('/locations/{location_id}', 'WarehouseLocationController@destroy');


    //仓库货区
    Route::get('/areas', 'WarehouseAreaController@index');
    Route::post('/areas', 'WarehouseAreaController@store');
    Route::put('/areas/{areas_id}', 'WarehouseAreaController@update');
    Route::delete('/areas/{areas_id}', 'WarehouseAreaController@destroy');


    //入库单分类
    Route::get('/batchType', 'BatchTypeController@index');
    Route::get('/batchType/{batch_id}', 'BatchTypeController@show');
    Route::post('/batchType', 'BatchTypeController@store');
    Route::put('/batchType/{type_id}', 'BatchTypeController@update');
    Route::delete('/batchType/{type_id}', 'BatchTypeController@destroy');

    //出库单分类
    Route::get('/orderType', 'OrderTypeController@index');
    Route::post('/orderType', 'OrderTypeController@store');
    Route::put('/orderType/{type_id}', 'OrderTypeController@update');
    Route::delete('/orderType/{type_id}', 'OrderTypeController@destroy');

    //发件人地址管理
    Route::get('/senderAddress', 'SenderAddressController@index');
    Route::post('/senderAddress', 'SenderAddressController@store');
    Route::put('/senderAddress/{address_id}', 'SenderAddressController@update');
    Route::delete('/senderAddress/{address_id}', 'SenderAddressController@destroy');
    Route::get('/senderAddress/{address_id}', 'SenderAddressController@show');

    //收件人地址管理
    Route::get('/receiverAddress', 'ReceiverAddressController@index');
    Route::post('/receiverAddress', 'ReceiverAddressController@store');
    Route::put('/receiverAddress/{address_id}', 'ReceiverAddressController@update');
    Route::delete('/receiverAddress/{address_id}', 'ReceiverAddressController@destroy');
    Route::get('/receiverAddress/{address_id}', 'ReceiverAddressController@show');

    //供应商管理
    Route::get('/distributor', 'DistributorController@index');
    Route::post('/distributor', 'DistributorController@store');
    Route::put('/distributor/{id}', 'DistributorController@update');
    Route::delete('/distributor/{id}', 'DistributorController@destroy');


    //商品
    Route::get('/products', 'ProductController@index');//货品库
    Route::get('/products/{product_id}', 'ProductController@show');
    Route::post('/products', 'ProductController@store');
    Route::put('/products/{product_id}', 'ProductController@update');
    Route::delete('/products/{product_id}', 'ProductController@destroy');
    Route::post('/products/import', 'ProductController@import');



    //商品规格
    Route::delete('/specs/{spec_id}', 'ProductSpecController@destroy');
    Route::post('/specs/import', 'ProductSpecController@import');
    Route::get('/specs', 'ProductSpecController@index');
    Route::post('/specs/locations', 'ProductStockController@getLocationBySpec');//规格找到货位


    //入库单
    Route::get('/batch', 'BatchController@index');
    Route::get('/batch/{batch_id}', 'BatchController@show');
    Route::post('/batch', 'BatchController@store');
    Route::put('/batch/{batch_id}', 'BatchController@update');
    Route::delete('/batch/{batch_id}', 'BatchController@destroy');
    Route::post('/batch/shelf', 'BatchController@shelf');
    Route::get('/batch/{batch_id}/download/{tempate}', 'BatchController@download');
    Route::get('/batch/{batch_id}/pdf/', 'BatchController@pdf');
    Route::get('/batch/{batch_id}/pdf/{tempate}', 'BatchController@pdf');
    Route::post('/batchCode', 'BatchController@batchCode');


    //出库单
    Route::get('/order', 'OrderController@index');
    Route::post('/order', 'OrderController@store');
    Route::put('/order/cancel/{order_id}', 'OrderController@cancelOrder');
    Route::put('/order/data/{order_id}', 'OrderController@updateData');
    Route::delete('/order/{order_id}', 'OrderController@destroy');
    Route::post('/order/out', 'OrderController@pickAndOut');//拣货和出库
    Route::get('/order/export', 'OrderController@export');
    Route::get('/order/{order_id}', 'OrderController@show');
    Route::put('/order/express/{order_id}', 'OrderController@updateExpress'); //更新快递单号
    Route::put('/order/pay/{order_id}', 'OrderController@updatePayStatus'); //更新支付方式
    Route::put('/order/completed/{order_id}', 'OrderController@completed'); //设为签收
    Route::get('/order/pay/status', 'OrderController@payStatusList'); //支付状态列表
    Route::get('/order/pay/type', 'OrderController@payTypeList'); //支付方式列表
    
    Route::get('/order/{id}/download/', 'OrderController@download');
    Route::get('/order/{id}/download/{tempate}', 'OrderController@download');
    Route::get('/order/{id}/pdf/', 'OrderController@pdf');
    Route::get('/order/{id}/pdf/{tempate}', 'OrderController@pdf');

    //库存
    // Route::get('/stock/code', 'ProductStockController@getSkus');
    Route::get('/stock/code', 'ProductStockController@getLocations');
    Route::get('/stock/sku/{sku}', 'ProductStockController@getInfoBySku');
    Route::put('/stock/{stock_id}', 'ProductStockController@update');//盘点
    Route::get('/stock/sku/log/{stock_id}', 'ProductStockController@getLogsForSku');
    Route::get('/stock/spec/log/{stock_id}', 'ProductStockController@getLogsForSpec');
    Route::get('/stock', 'ProductStockController@index');
    Route::get('/export/sku', 'ProductStockController@exportBySku');
    Route::get('/export/stock', 'ProductStockController@export');





    //员工分组管理
    Route::post('/group','GroupController@store'); //新增员工分组
    Route::put('/group/{group_id}','GroupController@update');
    Route::delete('/group/{group_id}','GroupController@destroy');
    Route::get('/group','GroupController@index');
    Route::get('/group/{group_id}','GroupController@show');

    //员工管理
    Route::post('/employee','EmployeeController@store'); //新增员工
    Route::put('/employee/{user_id}','EmployeeController@update');
    Route::delete('/employee/{user_id}','EmployeeController@destroy');
    Route::get('/employee','EmployeeController@index');
    Route::post('/employee/{user_id}/lock','EmployeeController@lock');

    Route::post('/relation', 'RelationController@store');//添加员工至分组
    Route::delete('/relation', 'RelationController@destroy');//从分组删除员工

    Route::post('/privilege', 'PrivilegeController@store');//给分组分配权限
    Route::put('/privilege', 'PrivilegeController@update');


    Route::post('warning','WarningController@store');
    Route::get('warning','WarningController@show');
    Route::delete('warning','WarningController@destroy');


    //店铺
    Route::get('/shop', 'ShopController@index');
    Route::post('/shop', 'ShopController@store');
    Route::get('/shop/{id}', 'ShopController@show');
    Route::put('/shop/{id}', 'ShopController@update');
    Route::delete('/shop/{id}', 'ShopController@destroy');

    //店铺详细
    Route::get('/shop/{shopId}/product', 'ShopProductController@index');
    Route::post('/shop/{shopId}/product', 'ShopProductController@store');
    Route::put('/shop/{shopId}/product/{id}', 'ShopProductController@update');
    Route::delete('/shop/{shopId}/product/{ids}', 'ShopProductController@destroy');
    Route::get('/shop/{shopId}/product/{id}', 'ShopProductController@show');
    Route::put('/shop/{shopId}/product', 'ShopProductController@onShelf');

    //默认发件人
    Route::get('/shop/{id}/sender', 'ShopController@senderShow');
    Route::post('/shop/{id}/sender', 'ShopController@senderUpdate');

    //店铺支付方式
    Route::get('/shop/payment', 'ShopPaymentMethodController@index');
    Route::post('/shop/payment', 'ShopPaymentMethodController@store');
    Route::put('/shop/payment/{id}', 'ShopPaymentMethodController@update');
    Route::delete('/shop/payment/{id}', 'ShopPaymentMethodController@destroy');

    //库存盘点
    Route::get('/recount', 'RecountController@index');
    Route::post('/recount', 'RecountController@store');
    Route::get('/recount/{id}', 'RecountController@show');
    Route::delete('/recount/{id}', 'RecountController@destroy');
    Route::get('/recount/{id}/download/', 'RecountController@download');
    Route::get('/recount/{id}/pdf/', 'RecountController@pdf');


});


$router->group(['prefix' => 'admin', 'namespace' => 'Admin'], function($router) {
    // 认证、授权
    $router->post('/auth', 'AuthController@login');// 登入

    $router->group(['middleware' => ['auth', 'role:admin']], function($router) {
        // 首页
        $router->get('/homePage', 'HomePageController@adminHomePage');// 列表
        $router->get('/homePageNotice', 'HomePageController@adminNotice');// 列表
        $router->get('/homePageTable', 'HomePageController@adminUsertable');// 列表

        // 管理员
        $router->get('/administrator', 'AdminController@list');// 列表
        $router->put('/administrator', 'AdminController@create');// 添加到管理员列表
        $router->delete('/administrator', 'AdminController@delete');// 移除出管理员列表

        // 用户
        $router->get('/user', 'UserController@list');// 列表
        $router->get('/user/{user_id}', 'UserController@show');// 详情
        $router->put('/user', 'UserController@store');// 创建
        $router->post('/user', 'UserController@edit');// 编辑
        $router->post('/user/lock', 'UserController@lock');// 禁用
        $router->post('/user/unlock', 'UserController@unlock');// 启用

    });
});

$router->group(['prefix' => 'open', 'namespace' => 'Open'], function($router) {

    Route::any('wechat', 'WeChatController@serve');
    Route::get('/express', 'ExpressController@list');//快递公司列表

});

$router->get('open/shop/list', 'Open\\Shop\\ShopController@index');// 店铺列表
//店铺开放型接口
$router->group(['prefix' => 'open/shop', 'namespace' => 'Open\\Shop', 'middleware' => ['shop']], function($router) {

    Route::post('/login', 'AuthenticateController@autoLogin')->name('openShopLogin');
    $router->get('/', 'ShopController@show');// 店铺详细
    $router->get('/categories', 'CategoryController@list');// 列表
    $router->get('/categories/{id}/products', 'ProductController@list');// 商品列表
    $router->get('/products/{id}', 'ProductController@show');// 商品详细

    $router->group(['middleware' => [ 'auth:shop']], function($router) {
        
        $router->get('/cart', 'CartController@list');// 购物车列表
        $router->get('/cart/count', 'CartController@count');// 购物车数量
        $router->post('/cart', 'CartController@store');// 加入购物车
        $router->put('/cart/{id}/{qty}', 'CartController@updateQty');// 修改购物车数量
        $router->delete('/cart/{id}', 'CartController@remove');// 删除单个购物车商品
        $router->delete('/cart', 'CartController@destroy');// 删除整个购物车商品
        $router->post('/cart/checkout', 'CartController@checkout');// 下单

        $router->get('/order', 'OrderController@list');// 店铺订单ID
        $router->get('/order/{id}', 'OrderController@show');// 店铺订单ID
    });

    

});
