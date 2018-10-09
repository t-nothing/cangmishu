<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
	// return app('translator')->getLocale();
	// return $router->app->version();
	// return app()->version();
	// return env('APP_ENV');
});

$router->post('/user/auth', 'AuthController@login');// 用户认证
$router->post('/user/register', 'UserController@store');// 用户注册
$router->get('/user/activate/{token_value}', [
	'as' => 'user-activation',
	'uses' => 'UserController@activate',
]);// 用户激活

$router->post('/user/forgetPassword', 'PasswordController@store');// 忘记密码-请求重置
$router->get('/user/resetPassword/{token_value}', [
	'as' => 'pwd-activation',
	'uses' => 'PasswordController@show',
]);// 忘记密码-重置密码链接
$router->post('/user/resetPassword', 'PasswordController@edit');// 忘记密码-重置密码接口

$router->post('/nladdress', 'NLAddressController@show');// 根据邮编门牌号拉取信息
/*
|--------------------------------------------------------------------------
| 开放接口
|--------------------------------------------------------------------------
*/

$router->group(['prefix' => 'open', 'namespace' => 'Open'], function () use($router) {
	$router->group(['middleware' => ['auth:sign', 'can:owner|renter']], function () use($router) {
		$router->post('/ping', 'OpenController@ping');// 验证 APPKEY 是否正确 

		// 出库单
		$router->post('/order', 'OrderController@store');// 新增
		$router->post('/order/appointment', 'OrderController@appointment');// 预约（可重复）
		$router->post('/order/cancel', 'OrderController@cancel');// 取消
		$router->post('/order/refund', 'OrderController@refund');// 退款

		$router->post('/order/delivery', 'OrderController@delivery');// 司机发货（配送中）
		$router->post('/order/failed_delivery', 'OrderController@failed_delivery');// 司机派送失败
		$router->post('/order/receipt', 'OrderController@receipt');// 客户签收（已签收）

		$router->post('/package', 'DeliveryController@package');
		$router->post('/package/delivery', 'DeliveryController@delivery');
		$router->post('/package/receipt', 'DeliveryController@receipt');

		// 查询出库单货品信息（保质期、生产批次号）
		$router->get('/order/item/info', 'OrderItemController@info');

		// 根据外部编码查询，根据最佳食用期和生产批次号分组
		$router->get('/sku', 'SkuController@retrieveByCode');

		// 库存
		$router->get('/stock/query', 'StockController@query');// 查询
		$router->get('/stock/total', 'StockController@total');// 批量查询，仓库库存
	});
});

/*
|--------------------------------------------------------------------------
| 商家端
|--------------------------------------------------------------------------
*/

$router->group(['middleware' => ['auth']], function () use($router) {
   //入库单code
   $router->post('/batch/batchCode','BatchController@batchCode');
   $router->get('/batch/generateSKU','BatchController@generateSKU');

   //首页数据
    $router->get('/home/notice', 'HomePageController@notice');// 首页通知
    $router->get('/home/analyze', 'HomePageController@analyze');// 首页仓库
    $router->get('/home/analyzeTable', 'HomePageController@batchOrOrderCount');// 首页仓库

    $router->post('/upload/image', 'UploadController@image');// 图片上传
    $router->post('/upload/pdf', 'UploadController@pdf');// PDF上传

    $router->get('/user/find', 'UserController@find');// 查找用户
	$router->get('/user/account', 'AuthController@me');// 用户账户信息
	$router->post('/user/editPassword', 'PasswordController@selfedit');// 个人中心-更改密码
	$router->post('/user/logout', 'AuthController@logout');// 用户登出

	$router->post('/user/warehouses', 'UserController@warehouseLists');// 用户仓库列表	
        $router->get('/user/setWarehouse/{warehouse_id}', 'UserController@setWarehouse');// 设为默认仓库

	// 发件人
	$router->get('/sender', 'UserSenderController@list');// 列表
	$router->get('/sender/{id}', 'UserSenderController@show');// 详情
	$router->put('/sender', 'UserSenderController@create');// 新增
	$router->post('/sender', 'UserSenderController@update');// 修改
	$router->post('/sender/{id}/setDefault', 'UserSenderController@setDefault');// 设为默认
	$router->delete('/sender', 'UserSenderController@delete');// 删除

	// 收件人
	$router->get('/receiver', 'UserReceiverController@list');// 列表
	$router->get('/receiver/{id}', 'UserReceiverController@show');// 详情
	$router->put('/receiver', 'UserReceiverController@create');// 新增
	$router->post('/receiver', 'UserReceiverController@update');// 修改
	$router->post('/receiver/{id}/setDefault', 'UserReceiverController@setDefault');// 设为默认
	$router->delete('/receiver', 'UserReceiverController@delete');// 删除

	// 入库单
	$router->get('/batch', 'BatchController@list');// 入库单列表
	$router->get('/batch/{batch_id}', 'BatchController@retrieveById');// 入库单详情
	$router->put('/batch', 'BatchController@store');// 入库单创建
	$router->delete('/batch', 'BatchController@delete');// 入库单删除

	$router->get('/batch/{batch_id}/pdf', [
		'as' => 'batch-pdf',
		'uses' => 'BatchController@pdf',
	]);// 入库单预览
	$router->get('/batch/{batch_id}/download', 'BatchController@download');// 入库单下载

	// 入库单 - 供应商
	$router->get('/distributor', 'DistributorController@list');
	$router->get('/distributor/{distributor_id}', 'DistributorController@show');
	$router->put('/distributor', 'DistributorController@store');
	$router->post('/distributor', 'DistributorController@edit');
	$router->delete('/distributor', 'DistributorController@delete');

	// 出库单
    $router->get('/order', 'OrderController@list');// 列表
    $router->get('/order/{order_id}', 'OrderController@show');// 详情
	$router->put('/order', 'OrderController@store');// 新增

	$router->delete('/origin', 'OriginController@delete');//删除产地
	$router->get('/origin', 'OriginController@list');//获取产地
	$router->post('/origin', 'OriginController@edit');//修改产地
	$router->put('/origin', 'OriginController@create');//增加产地

	// 员工管理
	$router->get('/employee', 'EmployeeController@list');// 列表
	$router->get('/employee/{user_id}', 'EmployeeController@show');// 详情
	$router->put('/employee', 'EmployeeController@create');// 添加
	$router->post('/employee', 'EmployeeController@update');// 修改
	$router->delete('/employee', 'EmployeeController@delete');// 删除

    $router->put('/owner/apply', 'UserCertificationController@ownerApply');// 仓库产权申请认证
    $router->get('/owner','LeaseApplicationInfoController@ownerApplyList');   //租赁申请列表
    $router->get('/owner/{lease_id}','LeaseApplicationInfoController@ownerApplyShow');     //仓库产权方租赁申请详情
    $router->post('/owner/check','LeaseApplicationInfoController@ownerCheck');             //产权方审核申请

    $router->put('/renters/apply', 'UserCertificationController@rentersApply');// 仓库租赁申请认证
    $router->get('/renters','LeaseApplicationInfoController@list');           //查看可申请仓库列表 ok
    $router->put('/renters','LeaseApplicationInfoController@rentersCreate');   //仓库租赁申请 ok
    $router->get('/renters/list','LeaseApplicationInfoController@rentersApplyList');//租赁者查看已经申请列表 ok
    $router->get('/renters/show/{lease_id}','LeaseApplicationInfoController@rentersApplyShow');//租赁者查看已经申请详情

	$router->get('/kep', 'KepController@list');                 //篮子列表
	$router->get('/kep/{kep_id}','KepController@show');       //托盘详情
	$router->put('/kep', 'KepController@create');               //新增篮子
	$router->post('/kep', 'KepController@edit');                //修改篮子
	$router->delete('/kep', 'KepController@delete');            //删除篮子

	$router->group(['middleware' => ['can:owner']], function () use($router) {
		// 分类
		$router->put('/category', 'CategoryController@create');// 增加
		$router->post('/category', 'CategoryController@edit');// 修改
		$router->delete('/category', 'CategoryController@delete');// 删除
	});
	
	$router->group(['middleware' => ['can:owner|renter']], function () use($router) {
		// 分类
		$router->get('/category', 'CategoryController@list');// 列表
		$router->get('/category/{id}', 'CategoryController@show');// 详情
		// 货品
		$router->get('/product', 'ProductController@list');//获取商品列表
		$router->post('/product', 'ProductController@edit');//修改商品
		$router->put('/product', 'ProductController@create');//添加商品
		$router->get('/product/{product_id}', 'ProductController@show');//获取单个商品信息
		$router->post('/product/setCategory', 'ProductController@setCategory');//批量设置商品分类

		// 规格
		$router->put('/spec', 'ProductSpecController@store');// 规格添加
		$router->get('/spec/{product_id}', 'ProductSpecController@list');// 获取单个货品规格
		$router->post('/spec', 'ProductSpecController@edit');// 规格修改
		$router->delete('/spec', 'ProductSpecController@delete');// 规格删除

		// 库存
		$router->get('/stockList','ProductStockController@index');//库存列表
		$router->get('/stock/log','ProductStockController@log');// 特定货品规格的出入库记录
		$router->get('/stock/log/sku','ProductStockController@getLogsForSku');// 特定 SKU 的出入库记录
	});

	// 仓库
    $router->get('/warehouse', 'WarehouseController@list');
    $router->get('/warehouse/{warehouseID}', 'WarehouseController@show');
    $router->put('/warehouse', 'WarehouseController@create');
    $router->post('/warehouse', 'WarehouseController@edit');

    // 仓库特性
    $router->get('/warehouseFeature', 'WarehouseFeatureController@list');
	$router->get('/warehouseFeature/{id}', 'WarehouseFeatureController@show');
    $router->put('/warehouseFeature', 'WarehouseFeatureController@create');
    $router->post('/warehouseFeature', 'WarehouseFeatureController@edit');
	$router->delete('/warehouseFeature', 'WarehouseFeatureController@delete');

	// 仓库货区
	$router->get('/warehouseLocation', 'WarehouseLocationController@list');
	$router->get('/warehouseLocation/{id}', 'WarehouseLocationController@show');
    $router->put('/warehouseLocation', 'WarehouseLocationController@create');
    $router->post('/warehouseLocation', 'WarehouseLocationController@edit');
	$router->delete('/warehouseLocation', 'WarehouseLocationController@delete');

    // 仓库角色管理
    $router->get('/warehouse/{warehouse_id}/role', 'WarehouseEmployeeRoleController@list');// 列表

    // 仓库员工管理
	$router->get('/warehouse/{warehouse_id}/employee', 'WarehouseEmployeeController@list');// 列表
	$router->put('/warehouse/{warehouse_id}/employee', 'WarehouseEmployeeController@create');// 添加
	$router->delete('/warehouse/{warehouse_id}/employee', 'WarehouseEmployeeController@delete');// 删除

    // 货区
    $router->get('/warehouseArea', 'WarehouseAreaController@list');//查询某仓库的货区列表
    $router->get('/warehouseArea/{warehouse_area_id}', 'WarehouseAreaController@show');//获取仓库货区信息
    $router->put('/warehouseArea', 'WarehouseAreaController@create');//创建仓库货区信息
    $router->post('/warehouseArea', 'WarehouseAreaController@edit');//修改仓库货区信息
    $router->delete('/warehouseArea', 'WarehouseAreaController@delete');//删除仓库货区信息

    $router->get('/functions', 'WarehouseAreaController@functions');// 仓库货区功能分类
    $router->get('/temperatures', 'WarehouseAreaController@temperatures');// 仓库货区温度分类

    $router->get('/batchType', 'BatchTypeController@list');//获取入库分类列表
    $router->post('/batchType', 'BatchTypeController@edit');//修改入库分类
    $router->put('/batchType', 'BatchTypeController@create');//添加入库分类
    $router->delete('/batchType', 'BatchTypeController@delete');//删除入库分类

    $router->get('/orderType', 'OrderTypeController@list');//获取入库分类列表
    $router->post('/orderType', 'OrderTypeController@edit');//修改入库分类
    $router->put('/orderType', 'OrderTypeController@create');//添加入库分类
    $router->delete('/orderType', 'OrderTypeController@delete');//删除入库分类

    $router->get('/warning/stock', 'WarningController@stocklist');//获取商品库存报警配置 列表
    $router->post('/warning/stock', 'WarningController@stockstore');//保存商品库存报警信息
    $router->get('/warning/expiration', 'WarningController@expirationlist');//获取商品保质期到期配置 列表
    $router->post('/warning/expiration', 'WarningController@expirationstore');//保存商品保质期到期报警信息

    $router->post('/getTrackInfo','TrackingController@getTrackInfo');// 包裹物流
});

/*
|--------------------------------------------------------------------------
| 管理员端
|--------------------------------------------------------------------------
*/

$router->group(['prefix' => 'admin', 'namespace' => 'Admin'], function($router) {
	// 认证、授权
	$router->post('/auth', 'AuthController@login');// 登入

	$router->group(['middleware' => ['auth', 'can:admin']], function($router) {
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

		// 创建仓库认证
		$router->get('/owner/application', 'UserOwnerCertificationApplicationController@list');
		$router->get('/owner/application/{id}', 'UserOwnerCertificationApplicationController@show');
	    $router->post('/owner/application', 'UserOwnerCertificationApplicationController@check');

		// 租赁仓库认证
		$router->get('/renters/application', 'UserRenterCertificationApplicationController@list');
		$router->get('/renters/application/{id}', 'UserRenterCertificationApplicationController@show');
	    $router->post('/renters/application', 'UserRenterCertificationApplicationController@check');
	});
});

/*
|--------------------------------------------------------------------------
| PC端
|--------------------------------------------------------------------------
*/

$router->group([
	'prefix' => 'pc',
	'namespace' => 'Pc',
	'middleware' => [
		'auth',
		'can:owner',
	]
], function($router) {
    // 拣货单
    $router->get('/pick', 'PickController@list');// 列表

    // 验货
    $router->post('/verify', 'VerifyController@show');// 获取商品详情
    $router->post('/confirm', 'VerifyController@confirm');// 完成验货

    // 打印
    $router->post('/print/pick', 'PickController@printPick');// 打印拣货单
    $router->post('/print/pack', 'PickController@printPack');// 打印打包单
    $router->post('/print/express', 'PickController@printExpress');// 打印快递单
    $router->post('/print/ean', 'ProductController@ean');// 打印EAN

    // 库存
    $router->get('/stock', 'StockController@list');// 商品列表
    $router->post('/stock', 'StockController@edit');
});

/*
|--------------------------------------------------------------------------
| 手持端
|--------------------------------------------------------------------------
*/

$router->group([
	'prefix' => 'terminal',
	'namespace' => 'Terminal',
	'middleware' => [
		'auth',
		'can:owner',
	]
], function($router) {
	// 入库
	$router->get('batch', 'BatchController@retrieveByCode');
    $router->get('product', 'BatchController@getProductByCode');
    $router->post('purStockNoteEnd', 'StockController@in');//确认商品入库
    $router->post('purchaseNoteEnd', 'BatchController@purchaseNoteEnd');//确认入库

    // 上架
    $router->get('/stock', 'StoreController@retrieveBySku');// 扫描 SKU 拉取商品详情
    
	$router->post('/store', 'StoreController@putOn');// 上架，绑定货位
    $router->post('/expressDefeated', 'OrderController@expressDefeated');//订单派送失败

    // 捡货
    $router->post('/pick/start','PickController@start');// 开始捡货，获取捡货数据
	$router->post('/pick/submit','PickController@submit');// 提交捡货结果
	$router->post('/pick/release','PickController@release');// 释放捡货单与篮子

	// 出库包裹复核记录
	$router->post('/pick/check/history', 'PickCheckHistoryController@create');

	// 仓库自提
	$router->put('/order/receiving','OrderController@receiving');// 确认收货

	// 库存核对
	$router->get('/sku', 'StockController@getSkus');// 查询库存记录（扫描货位或者SKU）
	$router->get('/stock/detail', 'StockController@show');// 详情
	$router->post('/stock/count', 'StockController@edit');// 库存盘点
	$router->post('/stock/off', 'StockController@off');// 作废库存记录
});
