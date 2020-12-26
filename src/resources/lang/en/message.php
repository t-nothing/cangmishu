<?php
return [
    '404NotFound' => 'Cannot find anything',
    'success' =>'Succeed',
    'failed' => 'Failed',
    'tokenInvalid' =>'Bad Request',
    'noPermission' =>'Bad Request',
    'activeAccount'=>'User is not activated, please activate your account by click at the link in your mail',
    'invalidOldPassword' => 'Origin password verify failed',

    'statusBatchIn'=>'Inbound',
    'statusShelf'=>'Putaway',
    'statusPick'=>'Picking',
    'statusOutbound'=>'Outbound',
    'statusRecount'=>'Inventory',

    'purchasePage'=>'Purchase',
    'purchasePageInvoiceNumber'=>'Invoice Number',
    'purchasePageArriveQty'=>'Confirm Quantity',

    'batchPage'=>'Inbound',
    'batchPageDate'=>'Date',
    'batchPageWarehouse'=>'Warehouse',
    'batchPageType'=>'Type',
    'batchPageDistributor'=>'Supplier',
    'batchPageRemark'=>'Remark',
    'batchPageNo'=>'No.',
    'batchPageSku'=>'SKU',
    'batchPageProductName'=>'Name',
    'batchPageSpecName'=>'Spec',
    'batchPagePurcharseQty'=>'Expected quantity',
    'batchPageActualQty'=>'Inbounded Quantity',
    'batchPageBatchNo'=>'Barcode',
    'batchPagePurcharsePrice'=>'Unit price',
    'batchPageInRemark'=>'Remark',
    'batchPageTreasurer'=>'Financial',
    'batchPageManager'=>'Manager',
    'batchPageKeeper'=>'Warehouse Keeper',
    'batchPageInDate'=>'Date',
    'batchPageEan'=>'Ean',
    'batchPageExpireAt'=>'EXP',//EXP
    'batchPageExpireBbd'=>'BBD',
    'batchPageExpireMfd'=>'Batch No.',

    'batchPurcharse'=>'Purcharse Requisition',
    'batchPurcharseDepartment'=>'Depertment',
    'batchPurcharsePrice'=>'Amount',
    'batchPurcharseTotal'=>'Total',
    'batchPurcharseRemark'=>'Remark',
    'batchPurcharseInitiator'=>'Initiator',
    'batchPurcharseDivisionHead'=>'Division Head',

    'batchPurchaseAddFailed' => 'Failed', #
    'batchPurchaseUpdateFailed' => 'Failed',#
    'batchPurchaseDeleteFailed' => 'Failed',#
    'batchPurchaseNotExist'=>'Information does not exist',
    'batchPurchaseCannotEdit'=>'Info is Readonly Now',
    'batchPurchaseCannotDelete' =>'Info is Readonly Now!',
    'batchPurchaseStatus1'=>'Purcharsing',
    'batchPurchaseStatus2'=>'Purcharsing',
    'batchPurchaseStatus3'=>'Done',
    'batchPurchaseItemStatus1'=>'Not Receive',
    'batchPurchaseItemStatus2'=>'Received Part',
    'batchPurchaseItemStatus3'=>'Received All',
    'batchPurchaseWarehouseNotExists'=>'Warehouse Not Exists',
    'batchPurchaseCodeExists'=>'Code Exists, Please Change',
    'batchPurchaseSupplierNotExists'=>'Supplier Not Exists',
    'batchPurchaseOuterCodeNotExists'=>'Spec Not Exists',
    'batchPurchasePriceRequired'=>'Price is Required',

    'batchAddFailed' => 'Failed', #
    'batchUpdateFailed' => 'Failed',#
    'batchDeleteFailed' => 'Failed',#
    'batchNotExist'=>'Information does not exist',
    'batchCannotEdit'=>'Info is Readonly Now',
    'batchOnshelfFailed'=>'Failed',
    'batchCannotDelete' =>'Only inbound orders with a status of "unCheck" can be deleted!',


    'batchTypeAddFailed' =>'Failed',
    'batchTypeUpdateFailed' => 'Failed',#
    'batchTypeNotExist'=>'Information does not exist',
    'batchTypeDeleteFailed' => 'Failed',#
    'batchTypeCannotDelete'=>'There is an inbound order under this inbound order, and deletion is not allowed.',

    'productCategoryAddFailed' =>'Failed',
    'productCategoryUpdateFailed' => 'Failed',#
    'productCategoryNotExist'=>'Information does not exist',
    'productCategoryDeleteFailed' => 'Failed',#
    'productCategoryCannotDelete'=>'Goods exist under this category, not allowed to delete',

    'distributorAddFailed' =>'Failed',
    'distributorUpdateFailed' => 'Failed',#
    'distributorNotExist'=>'Information does not exist',
    'distributorDeleteFailed' => 'Failed',#
    'distributorCannotDelete'=>'The goods exist under the supplier and are not allowed to be deleted.',

    'orderPage'=>'Outbound',
    'orderSalePage'=>'Order Sale',
    'orderPageDate'=>'Date',
    'orderPageSenderInfo'=>'Sender',
    'orderPageSenderName'=>'Fullname',
    'orderPageSenderPhone'=>'Phone',
    'orderPageSenderAddress'=>'Address',

    'orderPageReceiverInfo'=>'Receiver',
    'orderPageReceiverName'=>'Fullname',
    'orderPageReceiverPhone'=>'Phone',
    'orderPageReceiverAddress'=>'Address',
    'orderPageProduct'=>'Product & spec',
    'orderPageSaleProduct'=>'Product',
    'orderPageSaleSpec'=>'spec',
    'orderPageSaleReceiverInfo'=>'Receiver',
    'orderPageSaleReceiverName'=>'Fullname',
    'orderPageSaleReceiverPhone'=>'Phone',
    'orderPageSaleReceiverAddress'=>'Address',
    'orderPageSku'=>'Sku',
    'orderPageOrderQty'=>'Order Qty',
    'orderPageOutboundQty'=>'Outbound Qty',
    'orderPageSalePrice'=>'Price',
    'orderPageRemark'=>'Remark',
    'orderPageWarehouse'=>'Warehouse',
    'orderPageWarehouseLocation'=>'Rack',

    'orderPagePicking'=>'Picking List',
    'orderPagePickqty'=>'Picking Qty',

    'orderAddFailed' =>'Failed',
    'orderAddSuccess' =>'Success',
    'orderUpdateFailed' => 'Failed',#
    'orderNotExist'=>'Information does not exist',
    'orderDeleteFailed' => 'Failed',#
    'orderCannotDelete'=>'Failed',
    'orderExportSuccess'=>'Success',
    'orderExportCaption'=>'OrderExport',
    'orderPickingSuccess'=>'Success',
    'orderPickingFailed'=>'Failed :message',
    'orderStatusUnpay'=>'Unpay',
    'orderStatusRefund'=>'Refund',
    'orderStatusPaid'=>'Paid',
    'orderStatusUnConfirm'=>'To be confirmed',
    'orderStatusUnSend'=>'To be delivered',
    'orderStatusSending'=>'Delivery',
    'orderStatusSuccess'=>'Received',
    'orderPaymentAlipay'=>'Alipay',
    'orderPaymentWechat'=>'WeChat Pay',
    'orderPaymentBank'=>'Bank',
    'orderPaymentCash'=>'Cash',
    'orderPaymentOther'=>'Other',
    'orderStatusCancel'=>'Order cancelled',

    'orderStatusPicking'=>'Picking',
    'orderStatusOutbound'=>'Outbound',
    'orderVerifyNone'=>'UnVerify',
    'orderVerifyDone'=>'Verified',
    'orderVerifyWrong'=>'Verify wrong',

    'orderOpStopByUnPick'=>'Only when the picking is completed can the logistics information be modified.',

    'openOrderAddFailed' =>'Failed :message',
    'openOrderAddSuccess' =>'Success',
    'openOrderNotExist' =>'Information does not exist',
    'openOrderCancelFailed' =>'Current status does not support cancellation
',
    'openStockSearchOverThanMax' =>'Maximum support :num code query inventory
',

    'orderTypeAddFailed' =>'Failed',
    'orderTypeUpdateFailed' => 'Failed',#
    'orderTypeNotExist'=>'Information does not exist',
    'orderTypeDeleteFailed' => 'Failed',#
    'orderTypeCannotDelete'=>'There is an outbound order under this outbound order classification, and deletion is not allowed.',

    'userAddFailed' =>'Failed',
    'userUpdateFailed' => 'Failed',#
    'userEmailNotExist'=>'Email not exist',
    'userEmailSendSuccess'=>'The message has been sent, please check it',
    'userEmailTokenInvalid' =>'Retrieved information has expired or does not exist',
    'userNotExist'=>'User not exist',
    'userIsLocked' => 'Your account has been locked',#
    'userChangePasswordSuccess'=>'Success',
    'userChangePasswordFailed'=>'Failed',
    'userNewPassSameWithOld'  =>'Change the password to be different from the original password.',
    'userPassWordIsWrong'  =>'The original password is wrong',
    'userNameFormatInvalid'   =>  'Username can only contain numbers, letters, and underscores',
    'userSMSExpired'=>'Verification code has expired or does not exist',


    'userRegisterExpired'=>'Verification code has expired or does not exist',
    'userBindRepeat'=>'Please do not repeat the binding',
    'userRegisterSuccess'=>'Success,Welcome to Use WMS',
    'userRegisterEmailVerifyCodeFailed'=>'Image verification failed',
    'userRegisterSendSuccess'=>'Success',


    'productRelevanceCodeIsUsed'  =>'Sku :relevance_code Already used',
    'productAddFailed' =>'Success',
    'productNotExist'=>'Information does not exist',
    'productCannotDelete'=>'This item is not allowed to be deleted',
    'productUpdateFailed' => 'Failed',#
    'productDeleteFailed' => 'Failed',#
    'productSpecNotExists'=>'Spec Of product not found',
    'productSpecCannotDelete'=>'Do not allow delete :spec_name specification, stock under specification',
    'productImportsuccess'  =>'Import success',
    'productImportStop'=>'End of import, data verification failed',

    'productFieldName' =>'Product Name',
    'productFieldCategory' =>'Category',
    'productFieldWarehouse' =>'Warehouse',
    'productFieldSpecName' =>'Spec Name',
    'productFieldGrossWeight' =>'Gross Weight',
    'productFieldSalePrice' =>'Sale Price',
    'productFieldPurchasePrice' =>'Purchase Price',
    'productFieldRelevanceCode' =>'Relevance Code',

    'productSkuExportCaption'=>'SkuStock',
    'productStockExportCaption'=>'ProductStock',

    'receiverAddFailed' =>'Falied',
    'receiverUpdateFailed' => 'Failed',#
    'receiverNotExist'=>'Information does not exist',
    'receiverDeleteFailed' => 'Failed',#

    'recountPage' =>'Stock taking',
    'recountPageWarehouse' =>'Warehouse',
    'recountPageDate' =>'Date',
    'recountPageCreator' => 'Prepared by',
    'recountPageRemark' => 'Remark',
    'recountPageProductSpecName' => 'Name',
    'recountPageProductSpecSku' => 'Sku',
    'recountPageInboundBatch' => 'Inbound Batch',
    'recountPageOrginStock' => 'Original Stock',
    'recountPageInventoryCount' => 'Inventory Qty',
    'recountPageInventoryLoss' => 'Inventory loss',
    'recountPageInventoryLossMoney' => 'Fee Loss',
    'recountPageInventoryProfit' => 'Inventory profit',
    'recountPageInventoryProfitMoney' => 'Fee Profit',

    'recountAddFailed' =>'Failed',
    'recountUpdateFailed' => 'Failed',#
    'recountNotExist'=>'Information does not exist',
    'recountDeleteFailed' => 'Failed',#

    'senderAddFailed' =>'Failed',
    'senderUpdateFailed' => 'Failed',#
    'senderNotExist'=>'Information does not exist',
    'senderDeleteFailed' => 'Failed',#

    'shopAddFailed' =>'Failed',
    'shopUpdateFailed' => 'Failed',#
    'shopNotExist'=>'Information does not exist',
    'shopDeleteFailed' => 'Failed',#
    'shopMaxCountFailed' =>'Failed, Up to two stores in a warehouse',


    'shopProductAddFailed' =>'Failed',
    'shopProductUpdateFailed' => 'Failed',#
    'shopProductNotExist'=>'Information does not exist',
    'shopProductDeleteFailed' => 'Failed',#
    'shopProductShlefFailed' => 'Failed',#

    'warehouseAddFailed' =>'Failed',
    'warehouseUpdateFailed' => 'Failed',#
    'warehouseNotExist'=>'Information does not exist',
    'warehouseDeleteFailed' => 'Failed',#

    'warehouseAreaFieldNameCn' =>'Name',
    'warehouseAreaFieldNameCode' =>'Code',
    'warehouseAreaFieldNameIsEnabled'=>'Enabled',
    'warehouseAreaAddFailed' =>'Failed',
    'warehouseAreaUpdateFailed' => 'Failed',#
    'warehouseAreaNotExist'=>'Information does not exist',
    'warehouseAreaDeleteFailed' => 'Failed',#
    'warehouseAreaCannotDelete'=>'Inventory exists under this warehouse cargo area, not allowed to delete',

    'warehouseLocationAddFailed' =>'Failed',
    'warehouseLocationUpdateFailed' => 'Failed',#
    'warehouseLocationNotExist'=>'Information does not exist',
    'warehouseLocationNotExistExt'=>'The :code not exist',
    'warehouseLocationDeleteFailed' => 'Failed',#
    'warehouseLocationCannotDelete'=>'Inventory exists under this warehouse location, not allowed to delete',

    'warehouseFeatureAddFailed' =>'Failed',
    'warehouseFeatureUpdateFailed' => 'Failed',#
    'warehouseFeatureNotExist'=>'Information does not exist',
    'warehouseFeatureDeleteFailed' => 'Failed',#


    'warehouseWarningAddFailed' =>'Failed',
    'warehouseWarningUpdateFailed' => 'Failed',#
    'warehouseWarningNotExist'=>'Information does not exist',
    'warehouseWarningDeleteFailed' => 'Does not support deletion',#

    'appKeyAddFailed' =>'Failed',
    'appKeyUpdateFailed' => 'Failed',#
    'appKeyNotExist'=>'Information does not exist',
    'appKeyDeleteFailed' => 'Does not support deletion',#
];
