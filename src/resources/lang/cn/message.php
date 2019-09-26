<?php
return [
    '404NotFound' => '什么也没有找到',
    'success' =>'成功',
    'failed' => '操作失败', 
    'tokenInvalid' =>'无效请求',
    'noPermission' =>'没有权限',
    'activeAccount'=>'用户未激活，请登陆到邮箱激活',

    'batchAddFailed' => '添加入库单失败', #
    'batchUpdateFailed' => '修改入库单失败',#
    'batchDeleteFailed' => '删除入库单失败',#
    'batchNotExist'=>'入库单不存在',
    'batchCannotEdit'=>'该入库单不可编辑',
    'batchOnshelfFailed'=>'入库上架失败',
    'batchCannotDelete' =>'只能删除状态为‘待入库’的入库单!',


    'batchTypeAddFailed' =>'添加入库分类失败',
    'batchTypeUpdateFailed' => '修改入库分类失败',#
    'batchTypeNotExist'=>'入库分类不存在',
    'batchTypeDeleteFailed' => '删除入库单分类失败',#
    'batchTypeCannotDelete'=>'此入库单分类下存在入库单，不允许删除',
    
    'productCategoryAddFailed' =>'添加货品分类失败',
    'productCategoryUpdateFailed' => '修改货品分类失败',#
    'productCategoryNotExist'=>'货品货品分类不存在',
    'productCategoryDeleteFailed' => '删除货品分类失败',#
    'productCategoryCannotDelete'=>'该分类下存在货品，不允许删除',

    'distributorAddFailed' =>'添加供应商失败',
    'distributorUpdateFailed' => '修改供应商失败',#
    'distributorNotExist'=>'货品供应商不存在',
    'distributorDeleteFailed' => '删除供应商失败',#
    'distributorCannotDelete'=>'该供应商下存在货品，不允许删除',

    'orderAddFailed' =>'添加出库单失败',
    'orderAddSuccess' =>'添加出库单成功',
    'orderUpdateFailed' => '修改供应商失败',#
    'orderNotExist'=>'出库不存在',
    'orderDeleteFailed' => '删除出库单失败',#
    'orderCannotDelete'=>'出库单无法删除',
    'orderExportSuccess'=>'出库单导出已成功',
    'orderExportCaption'=>'仓秘书订单导出',
    'orderPickingSuccess'=>'拣货成功',
    'orderPickingFailed'=>'出库拣货失败 :message',
    'orderStatusUnpay'=>'未支付',
    'orderStatusRefund'=>'已退款',
    'orderStatusPaid'=>'已支付',
    'orderStatusUnConfirm'=>'待确认',
    'orderStatusUnSend'=>'待发货',
    'orderStatusSending'=>'配送中',
    'orderStatusSuccess'=>'已签收',
    'orderPaymentAlipay'=>'支付宝支付',
    'orderPaymentWechat'=>'微信支付',
    'orderPaymentBank'=>'银行卡支付',
    'orderPaymentCash'=>'现金支付',
    'orderPaymentOther'=>'其他方式',
    'orderStatusCancel'=>'订单取消',
    'orderOpStopByUnPick'=>'只有拣货完成才能修改物流信息',

    'openOrderAddFailed' =>'下单失败 :message',
    'openOrderAddSuccess' =>'下单成功',
    'openOrderNotExist' =>'订单不存在',
    'openOrderCancelFailed' =>'当前状态不支持取消',
    'openStockSearchOverThanMax' =>'最大只支持:num个code查询库存',

    'orderTypeAddFailed' =>'添加出库分类失败',
    'orderTypeUpdateFailed' => '修改出库分类失败',#
    'orderTypeNotExist'=>'出库分类不存在',
    'orderTypeDeleteFailed' => '删除出库单分类失败',#
    'orderTypeCannotDelete'=>'此出库单分类下存在出库单，不允许删除',

    'userAddFailed' =>'添加出库分类失败',
    'userUpdateFailed' => '修改出库分类失败',#
    'userEmailNotExist'=>'邮箱不存在',
    'userEmailSendSuccess'=>'邮件已发送, 请注意查收',
    'userEmailTokenInvalid' =>'找回信息已过期或不存在',
    'userNotExist'=>'用户信息不存在',
    'userIsLocked' => '您的帐户已被锁定',#
    'userChangePasswordSuccess'=>'重置密码成功',
    'userChangePasswordFailed'=>'重置密码失败',
    'userNewPassSameWithOld'  =>'修改密码需不同与原密码',
    'userPassWordIsWrong'  =>'原密码错误',
    'userNameFormatInvalid'   =>  '用户名只能包含数字、字母和下划线',
    'userSMSExpired'=>'短信验证码已过期',

    'userRegisterExpired'=>'验证码已过期或不存在',
    'userBindRepeat'=>'请不要重复绑定',
    'userRegisterSuccess'=>'注册成功,欢迎使用仓秘书',
    'userRegisterEmailVerifyCodeFailed'=>'图片验证失败',
    'userRegisterSendSuccess'=>'发送成功',


    'productRelevanceCodeIsUsed'  =>'SKU编码 :relevance_code 已被使用, 请更换',
    'productAddFailed' =>'添加商品成功',
    'productNotExist'=>'商品不存在',
    'productCannotDelete'=>'不允许删除此商品',
    'productUpdateFailed' => '修改商品失败',#
    'productDeleteFailed' => '删除商品失败',#
    'productSpecNotExists'=>'商品规格不存在',
    'productSpecCannotDelete'=>'不允许删除:spec_name 规格,规格下面的库存',
    'productImportsuccess'  =>'商品导入成功',
    'productImportStop'=>'导入结束,数据验证未通过',

    'productSkuExportCaption'=>'仓秘书SKU库存',
    'productStockExportCaption'=>'仓秘书货品总库存',

    'receiverAddFailed' =>'添加收件人信息失败',
    'receiverUpdateFailed' => '修改收件人地址失败',#
    'receiverNotExist'=>'收件地址不存在',
    'receiverDeleteFailed' => '删除收件地址失败',#

    'recountAddFailed' =>'添加盘点单信息失败',
    'recountUpdateFailed' => '修改盘点单地址失败',#
    'recountNotExist'=>'盘点单不存在',
    'recountDeleteFailed' => '删除盘点单失败',#

    'senderAddFailed' =>'添加发件人信息失败',
    'senderUpdateFailed' => '修改发件人地址失败',#
    'senderNotExist'=>'发件人地址不存在',
    'senderDeleteFailed' => '删除发件人地址失败',#

    'shopAddFailed' =>'添加店铺信息失败',
    'shopUpdateFailed' => '修改店铺失败',#
    'shopNotExist'=>'店铺不存在',
    'shopDeleteFailed' => '删除店铺失败',#

    'shopProductAddFailed' =>'添加店铺商品信息失败',
    'shopProductUpdateFailed' => '修改店铺商品失败',#
    'shopProductNotExist'=>'店铺商品不存在',
    'shopProductDeleteFailed' => '删除店铺商品失败',#
    'shopProductShlefFailed' => '上下架店铺商品失败',#

    'warehouseAddFailed' =>'添加仓库信息失败',
    'warehouseUpdateFailed' => '修改仓库失败',#
    'warehouseNotExist'=>'仓库不存在',
    'warehouseDeleteFailed' => '删除仓库失败',#

    'warehouseAreaAddFailed' =>'添加仓库货区失败',
    'warehouseAreaUpdateFailed' => '修改仓库货区失败',#
    'warehouseAreaNotExist'=>'仓库货区不存在',
    'warehouseAreaDeleteFailed' => '删除仓库货区失败',#
    'warehouseAreaCannotDelete'=>'此仓库货区下存在库存，不允许删除',

    'warehouseLocationAddFailed' =>'添加仓库货位失败',
    'warehouseLocationUpdateFailed' => '修改仓库货位失败',#
    'warehouseLocationNotExist'=>'仓库货位不存在',
    'warehouseLocationNotExistExt'=>'仓库货位:code不存在',
    'warehouseLocationDeleteFailed' => '删除仓库货位失败',#
    'warehouseLocationCannotDelete'=>'此仓库货位下存在库存，不允许删除',

    'warehouseFeatureAddFailed' =>'添加仓库特性失败',
    'warehouseFeatureUpdateFailed' => '修改仓库特性失败',#
    'warehouseFeatureNotExist'=>'仓库特性不存在',
    'warehouseFeatureDeleteFailed' => '删除仓库特性失败',#


    'warehouseWarningAddFailed' =>'添加仓库特性失败',
    'warehouseWarningUpdateFailed' => '修改仓库特性失败',#
    'warehouseWarningNotExist'=>'仓库特性不存在',
    'warehouseWarningDeleteFailed' => '不支持删除预警',#

    'appKeyAddFailed' =>'添加APP KEY失败',
    'appKeyUpdateFailed' => '修改APP KEY失败',#
    'appKeyNotExist'=>'APP KEY不存在',
    'appKeyDeleteFailed' => '不支持删除',#
];
