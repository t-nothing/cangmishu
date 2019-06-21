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
  $redis= app('redis');
  dd($redis);
});


Route::post('/login', 'AuthController@login');
Route::post('/logout', 'AuthController@logout');
Route::post('/register', 'UserController@register');
Route::post('/code', 'UserController@getCode');

Route::middleware(['auth:jwt'])->group(function () {

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
    Route::get('/products', 'ProductController@index');
    Route::get('/products/{product_id}', 'ProductController@show');
    Route::post('/products', 'ProductController@store');
    Route::put('/products/{product_id}', 'ProductController@update');
    Route::delete('/products/{product_id}', 'ProductController@destroy');
    Route::post('/products/import', 'ProductController@import');



    //商品规格
    Route::delete('/specs/{spec_id}', 'ProductSpecController@destroy');
    Route::post('/specs/import', 'ProductSpecController@import');


    //入库单
    Route::get('/batch', 'BatchController@index');
    Route::get('/batch/{batch_id}', 'BatchController@show');
    Route::post('/batch', 'BatchController@store');
    Route::put('/batch/{batch_id}', 'BatchController@update');
    Route::delete('/batch/{batch_id}', 'BatchController@destroy');
    Route::post('/batch/shelf', 'BatchController@shelf');
    Route::get('/batch/{batch_id}/download', 'BatchController@download');
    Route::get('/batch/{batch_id}/pdf', 'BatchController@pdf');


    //出库单
    Route::get('/order', 'OrderController@index');
    Route::post('/order', 'OrderController@store');
    Route::put('/order/status/{order_id}', 'OrderController@updateStatus');
    Route::put('/order/data/{order_id}', 'OrderController@updateData');
    Route::delete('/order/{order_id}', 'OrderController@destroy');
    Route::post('/order/out', 'OrderController@pickAndOut');
    Route::get('/order/{order_id}', 'OrderController@show');


    //库存
    Route::get('/stock/code', 'ProductStockController@getSkus');
    Route::get('/stock/sku/{sku}', 'ProductStockController@getInfoBySku');
    Route::put('/stock/{stock_id}', 'ProductStockController@update');
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


});



