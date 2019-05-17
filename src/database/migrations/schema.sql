-- MySQL dump 10.13  Distrib 5.7.21-20, for Linux (x86_64)
--
-- Host: 192.168.0.199    Database: dev_nle_wms_v2
-- ------------------------------------------------------
-- Server version	5.7.21-20

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!50717 SELECT COUNT(*) INTO @rocksdb_has_p_s_session_variables FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'performance_schema' AND TABLE_NAME = 'session_variables' */;
/*!50717 SET @rocksdb_get_is_supported = IF (@rocksdb_has_p_s_session_variables, 'SELECT COUNT(*) INTO @rocksdb_is_supported FROM performance_schema.session_variables WHERE VARIABLE_NAME=\'rocksdb_bulk_load\'', 'SELECT 0') */;
/*!50717 PREPARE s FROM @rocksdb_get_is_supported */;
/*!50717 EXECUTE s */;
/*!50717 DEALLOCATE PREPARE s */;
/*!50717 SET @rocksdb_enable_bulk_load = IF (@rocksdb_is_supported, 'SET SESSION rocksdb_bulk_load = 1', 'SET @rocksdb_dummy_bulk_load = 0') */;
/*!50717 PREPARE s FROM @rocksdb_enable_bulk_load */;
/*!50717 EXECUTE s */;
/*!50717 DEALLOCATE PREPARE s */;

--
-- Table structure for table `batch`
--

DROP TABLE IF EXISTS `batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_code` varchar(255) NOT NULL COMMENT '入库单号',
  `plan_time` int(11) DEFAULT NULL COMMENT '预计入库时间',
  `over_time` int(11) DEFAULT NULL,
  `type_id` int(11) NOT NULL COMMENT '入库方式类型',
  `distributor_id` int(11) DEFAULT NULL COMMENT '供应商id',
  `warehouse_id` int(11) DEFAULT '1' COMMENT '仓库id',
  `operator` int(11) NOT NULL COMMENT '操作人',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `need_num` int(11) DEFAULT '0' COMMENT '需要入库数量',
  `remarks` text COMMENT '备注',
  `owner_id` int(11) DEFAULT NULL COMMENT '属于那个商家的',
  `status` int(11) NOT NULL COMMENT '状态',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='入库表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `batch_log`
--

DROP TABLE IF EXISTS `batch_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batch_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` tinyint(4) NOT NULL,
  `relevance_code` varchar(255) NOT NULL,
  `ean` varchar(255) DEFAULT NULL,
  `num` int(11) NOT NULL COMMENT '数量',
  `balance_num` int(11) NOT NULL COMMENT '余额数量',
  `operator` int(11) NOT NULL COMMENT '操作人',
  `warehouse_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL COMMENT '接入商家的唯一标识',
  `order_sn` varchar(255) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='出库与入库记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '类目id',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名称',
  `warning_stock` int(11) DEFAULT NULL COMMENT '预警的数量',
  `parent_id` int(11) DEFAULT NULL COMMENT '上级分类id',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL COMMENT '更新时间（推荐）',
  `deleted_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统类目表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `distributor`
--

DROP TABLE IF EXISTS `distributor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `distributor` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '全名',
  `phone` varchar(50) NOT NULL COMMENT '电话',
  `email` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL COMMENT '国家',
  `city` varchar(50) NOT NULL COMMENT '城市',
  `street` varchar(100) NOT NULL COMMENT '街道',
  `door_no` varchar(50) DEFAULT NULL COMMENT '门牌号',
  `postcode` varchar(50) DEFAULT NULL COMMENT '邮政编码',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `distributor_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='供应商表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `goods_base`
--

DROP TABLE IF EXISTS `goods_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goods_base` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(256) NOT NULL,
  `name_cn` varchar(50) DEFAULT '' COMMENT '中文名',
  `name_en` varchar(200) DEFAULT '' COMMENT '英文名',
  `deleted_at` datetime DEFAULT NULL,
  `pic_url` varchar(255) DEFAULT NULL COMMENT '图片地址',
  `warehouse_id` tinyint(8) NOT NULL DEFAULT '0' COMMENT '仓库id',
  `relevance_code` varchar(64) NOT NULL COMMENT '货物代码',
  `owner_id` int(11) NOT NULL COMMENT '属于那个合作商的',
  `category_id` int(16) DEFAULT '0' COMMENT '类别ID',
  `brand_id` int(16) DEFAULT '0' COMMENT '品牌ID',
  `ean` varchar(255) DEFAULT NULL COMMENT 'ean码',
  `net_weight` int(11) DEFAULT '0' COMMENT '净重',
  `gross_weight` int(16) DEFAULT NULL COMMENT '毛重',
  `origin` varchar(255) DEFAULT NULL COMMENT '原产地',
  `out_goods_url` varchar(255) DEFAULT NULL COMMENT '商品第三方URL',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注信息',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `goods_base_sku_uindex` (`sku`),
  UNIQUE KEY `unique` (`warehouse_id`,`relevance_code`,`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品基础信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `goods_info`
--

DROP TABLE IF EXISTS `goods_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goods_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ean` varchar(255) NOT NULL COMMENT 'ean码',
  `sku` int(11) NOT NULL COMMENT 'sku',
  `distributor_id` int(11) NOT NULL COMMENT '供货商id',
  `warehouse_id` int(11) DEFAULT NULL COMMENT '仓库id',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '仓库库存数量',
  `expiration_date` datetime NOT NULL COMMENT '保质期',
  `status` tinyint(4) DEFAULT NULL COMMENT '商品的状态',
  `cost` decimal(8,4) NOT NULL DEFAULT '0.0000' COMMENT '成本价',
  `relevance_code` varchar(255) NOT NULL DEFAULT '0' COMMENT '关联码',
  `lock_num` int(16) DEFAULT '0' COMMENT '库存锁定数量',
  `salable_num` int(16) DEFAULT '0' COMMENT '可以销售的数量',
  `tray_id` int(11) DEFAULT '0' COMMENT '托盘号',
  `batch_id` varchar(32) DEFAULT '1' COMMENT '批次号',
  `type_id` int(11) DEFAULT '0' COMMENT '分区类型',
  `distributor_code` varchar(255) DEFAULT '0' COMMENT '供货商商品编码',
  `gtin` varchar(255) DEFAULT NULL,
  `units` tinyint(4) DEFAULT '1' COMMENT '单位',
  `spec_num` int(11) DEFAULT NULL COMMENT '单位数量',
  `spec_value` varchar(255) DEFAULT NULL COMMENT '单位值',
  `box_code` varchar(255) DEFAULT NULL COMMENT '箱子编码',
  `units_num` int(11) DEFAULT '0' COMMENT '已入数量',
  `need_num` int(11) DEFAULT '0' COMMENT '需要数量',
  `major_photo` varchar(255) DEFAULT NULL COMMENT '图片',
  `is_purchase_note` tinyint(4) DEFAULT '0' COMMENT '是否入库完成',
  `net_weight` int(16) DEFAULT NULL COMMENT '净重',
  `gross_weight` int(16) DEFAULT NULL COMMENT '毛重',
  `warning_num` int(16) DEFAULT NULL COMMENT '库存不足预警值',
  `inbound_num` int(16) DEFAULT '0' COMMENT '入库次数',
  `outbound_num` int(16) DEFAULT '0' COMMENT '出库次数',
  `origin` varchar(255) DEFAULT NULL COMMENT '原产地',
  `out_goods_url` varchar(255) DEFAULT NULL COMMENT '商品第三方URL',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注信息',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品详细';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `goods_ski`
--

DROP TABLE IF EXISTS `goods_ski`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goods_ski` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_info_id` int(11) DEFAULT NULL,
  `ski_code` varchar(255) DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kep`
--

-- DROP TABLE IF EXISTS `kep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
-- CREATE TABLE `kep` (
--   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '篮子id',
--   `name` varchar(255) NOT NULL COMMENT '篮子名',
--   `remark` varchar(255) DEFAULT NULL COMMENT '备注',
--   `shipment_num` varchar(255) NOT NULL COMMENT '捡货单',
--   `warehouse_id` int(11) NOT NULL,
--   `created_at` int(11) DEFAULT NULL,
--   `updated_at` int(11) DEFAULT NULL,
--   PRIMARY KEY (`id`),
--   UNIQUE KEY `kep_name` (`name`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='篮子表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order`
--

DROP TABLE IF EXISTS `order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `out_sn` varchar(64) NOT NULL COMMENT '外部编号，一般是订单ID',
  `source` varchar(32) DEFAULT NULL COMMENT '来源',
  `operator` int(11) NOT NULL COMMENT '操作人',
  `status` int(16) DEFAULT '1' COMMENT '状态值，1：待捡货，2：待发货，3：已发货',
  `remark` varchar(255) DEFAULT NULL COMMENT '买家备注',
  `shop_remark` varchar(255) DEFAULT NULL COMMENT '商家备注',
  `express_code` varchar(32) DEFAULT NULL COMMENT '快递公司编码',
  `delivery_date` int(11) DEFAULT NULL COMMENT '派送日期',
  `delivery_type` tinyint(2) DEFAULT '1' COMMENT '配送方式',
  `receiver_country` varchar(32) DEFAULT NULL,
  `receiver_city` varchar(64) DEFAULT NULL,
  `receiver_postcode` varchar(16) DEFAULT NULL,
  `receiver_doorno` varchar(32) DEFAULT NULL,
  `receiver_address` text,
  `receiver_fullname` varchar(32) DEFAULT NULL,
  `receiver_phone` varchar(255) DEFAULT NULL,
  `receiver_email` varchar(64) DEFAULT NULL,
  `is_night` tinyint(4) DEFAULT '0' COMMENT '是否夜间配送',
  `is_weekend` tinyint(4) DEFAULT '0' COMMENT '是否周末配送',
  `payment_fee` decimal(8,2) DEFAULT NULL,
  `total_fee` decimal(8,2) DEFAULT '0.00' COMMENT '总价',
  `coupon_fee` decimal(8,2) DEFAULT '0.00' COMMENT '优惠券金额',
  `coupon_name` varchar(255) DEFAULT NULL COMMENT '优惠券活动名',
  `is_plan_erp` tinyint(2) DEFAULT '0' COMMENT '是否erp预约',
  `old_plan_status` smallint(4) DEFAULT '0' COMMENT '取消预约前的订单状态',
  `invoice_number` varchar(255) DEFAULT NULL COMMENT '发票单号',
  `invoice_title` varchar(255) DEFAULT NULL,
  `invoice_content` varchar(255) DEFAULT NULL,
  `vip_id` int(11) NOT NULL COMMENT '用户id',
  `owner_id` int(11) NOT NULL COMMENT '接入商唯一标识',
  `send_country` varchar(255) DEFAULT NULL,
  `send_city` varchar(255) DEFAULT NULL,
  `send_postcode` varchar(255) DEFAULT NULL,
  `send_doorno` varchar(64) DEFAULT NULL,
  `send_address` text,
  `send_fullname` varchar(64) DEFAULT NULL,
  `send_phone` varchar(32) DEFAULT NULL,
  `receiver_province` varchar(64) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `output_time` int(11) DEFAULT NULL COMMENT '出库日期',
  PRIMARY KEY (`id`),
  UNIQUE KEY `out_sn` (`out_sn`,`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_item`
--

DROP TABLE IF EXISTS `order_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_item` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `order_id` int(16) NOT NULL COMMENT '订单号',
  `order_sku` int(16) DEFAULT '0' COMMENT '订单中的sku',
  `name` varchar(255) DEFAULT NULL COMMENT '商品名字',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `wms_sku` int(16) NOT NULL COMMENT '仓库sku',
  `amount` int(16) DEFAULT '0' COMMENT '数量',
  `weight` int(16) DEFAULT '0' COMMENT '单位g',
  `shipment_num` varchar(64) DEFAULT NULL COMMENT '发货单号/拣货单号',
  `express_num` varchar(64) DEFAULT NULL COMMENT '快递单号',
  `created_at` int(11) NOT NULL,
  `send_date` int(11) NOT NULL COMMENT '发货时间',
  `price` decimal(8,2) DEFAULT '0.00' COMMENT '商品价格',
  `line_id` int(11) DEFAULT NULL COMMENT '路线id',
  `line_name` varchar(255) DEFAULT NULL COMMENT '路线名称',
  `verify_num` int(11) DEFAULT '0' COMMENT '验货数量',
  `relevance_code` varchar(64) DEFAULT NULL COMMENT '商品自定义编号',
  `goods_info_id` varchar(255) DEFAULT NULL COMMENT 'goods_info表中的id,可能存在多个，所以用json了',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单详情表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `privilege`
--

DROP TABLE IF EXISTS `privilege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
-- CREATE TABLE `privilege` (
--   `id` int(11) NOT NULL AUTO_INCREMENT,
--   `name` varchar(256) NOT NULL DEFAULT '' COMMENT '权限名称',
--   `description` varchar(256) NOT NULL DEFAULT '' COMMENT '权限描述',
--   `type` int(11) NOT NULL COMMENT '权限类型\n1 平台系统权限\n2 仓库管理员权限\n3 商家权限',
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `privilege_rely_on`
--

DROP TABLE IF EXISTS `privilege_rely_on`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
-- CREATE TABLE `privilege_rely_on` (
--   `id` int(11) NOT NULL AUTO_INCREMENT,
--   `privilege_id` int(11) NOT NULL COMMENT '权限ID\n',
--   `relied_on` int(11) NOT NULL DEFAULT '0' COMMENT '依赖权限ID',
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限依赖关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
-- CREATE TABLE `role` (
--   `id` int(11) NOT NULL AUTO_INCREMENT,
--   `name` varchar(256) NOT NULL DEFAULT '' COMMENT '权限组名称',
--   `description` varchar(1024) NOT NULL DEFAULT '' COMMENT '权限组描述',
--   `type` int(11) NOT NULL COMMENT '权限组类型\n1 系统级\n2 仓库创建者\n3 仓库租用者',
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限组';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role_privilege`
--

DROP TABLE IF EXISTS `role_privilege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
-- CREATE TABLE `role_privilege` (
--   `role_id` int(11) DEFAULT NULL COMMENT '权限组ID',
--   `privilege_id` int(11) DEFAULT NULL COMMENT '权限ID'
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组权限详细';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shelf`
--

DROP TABLE IF EXISTS `shelf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shelf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '货架名',
  `warehouse_id` int(11) NOT NULL COMMENT '仓库id',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='货架表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `token`
--

DROP TABLE IF EXISTS `token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token_value` varchar(256) NOT NULL DEFAULT '' COMMENT 'token值',
  `token_type` int(11) NOT NULL COMMENT 'token类型\n1 access token\n2 email confirm \n3 forget password  \n4 activate ',
  `expired_at` int(11) NOT NULL DEFAULT '0' COMMENT '过期时间',
  `owner_user_id` int(11) NOT NULL COMMENT '所属用户ID',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `access_at` int(11) NOT NULL DEFAULT '0' COMMENT '访问/使用时间',
  `is_valid` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Token表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tray`
--

DROP TABLE IF EXISTS `tray`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
-- CREATE TABLE `tray` (
--   `id` int(11) NOT NULL AUTO_INCREMENT,
--   `name` varchar(255) NOT NULL COMMENT '托盘名字',
--   `shelf_id` int(11) NOT NULL COMMENT '货架号',
--   `plies` int(11) DEFAULT '0' COMMENT '所在层数',
--   `place` varchar(11) DEFAULT '0' COMMENT '所在位置',
--   `created_at` datetime NOT NULL,
--   `updated_at` datetime DEFAULT NULL,
--   `status` int(11) NOT NULL COMMENT '托盘状态',
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='托盘表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(256) NOT NULL DEFAULT '',
  `password_digest` varchar(256) NOT NULL DEFAULT '' COMMENT '密码hash',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_uindex` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_certification`
--

DROP TABLE IF EXISTS `user_certification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
-- CREATE TABLE `user_certification` (
--   `id` int(11) NOT NULL AUTO_INCREMENT,
--   `user_id` int(11) NOT NULL COMMENT '申请人 user_id',
--   `type` int(11) NOT NULL COMMENT '申请类型\n1 创建仓库\n2 使用仓库',
--   `status` int(11) DEFAULT '1' COMMENT '认证状态\n1 待审核\n2 通过\n3 驳回',
--   `name_cn` varchar(255) NOT NULL DEFAULT '' COMMENT '仓库中文名',
--   `name_en` varchar(255) NOT NULL DEFAULT '' COMMENT '仓库英文名',
--   `warehouse_owner` varchar(255) NOT NULL DEFAULT '' COMMENT '仓库产权方',
--   `phone` varchar(255) NOT NULL DEFAULT '' COMMENT '联系电话',
--   `country` varchar(255) DEFAULT '' COMMENT '国家',
--   `postcode` varchar(255) NOT NULL DEFAULT '' COMMENT '邮编',
--   `door_no` varchar(255) DEFAULT '' COMMENT '门牌号',
--   `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
--   `street` varchar(255) NOT NULL DEFAULT '' COMMENT '街道',
--   `warehouse_plan` varchar(255) NOT NULL DEFAULT '' COMMENT '平面图',
--   `kvk_code` varchar(255) NOT NULL DEFAULT '' COMMENT 'KVK商会注册码',
--   `vat_code` varchar(255) NOT NULL DEFAULT '' COMMENT 'VAT号码',
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='申请认证';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_extra`
--

DROP TABLE IF EXISTS `user_extra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
-- CREATE TABLE `user_extra` (
--   `user_id` int(11) NOT NULL,
--   `is_certificated_creator` tinyint(1) DEFAULT '0' COMMENT '是否认证仓库创建者',
--   `is_certificated_renter` tinyint(1) DEFAULT '0' COMMENT '是否认证租赁方',
--   `self_use_limit` int(11) DEFAULT '0' COMMENT '自用仓库限制',
--   `share_limit` int(11) DEFAULT '0' COMMENT '共享仓库限制',
--   `is_auto_verify_self_use` tinyint(1) DEFAULT '0' COMMENT '是否自动审核自用仓库',
--   `is_auto_verify_share` tinyint(1) DEFAULT '0' COMMENT '是否自动审核共享仓库'
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户附加信息表';
/*!40101 SET character_set_client = @saved_cs_client */;


-- DROP TABLE IF EXISTS `user_warehouse_role`;
--
-- CREATE TABLE `user_warehouse_role` (
--   `id` INT(11) NOT NULL AUTO_INCREMENT,
--   `user_id` INT(11) NOT NULL,
--   `warehouse_id` INT(11) NOT NULL,
--   `role_id` INT(11) NOT NULL,
--   PRIMARY KEY (`id`)
-- ) ENGINE = InnoDB COMMENT = '用户仓库角色表';

--
-- Table structure for table `warehouse`
--

DROP TABLE IF EXISTS `warehouse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL DEFAULT '' COMMENT '仓库名称',
  `owner_id` int(11) NOT NULL COMMENT '创建者用户ID',
  `type` int(11) NOT NULL COMMENT '仓库类型\n	1 共享仓库\n	2 私有仓库',
  `code` varchar(255) NOT NULL DEFAULT '' COMMENT '仓库代码',
  `country` varchar(255) NOT NULL DEFAULT '' COMMENT '国家',
  `city` varchar(50) DEFAULT NULL COMMENT '城市',
  `street` varchar(100) DEFAULT NULL COMMENT '街道',
  `door_no` varchar(50) DEFAULT NULL COMMENT '门牌号',
  `postcode` varchar(50) DEFAULT NULL COMMENT '邮政编码',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` int(11) DEFAULT NULL COMMENT '仓库状态\n	1 待审核\n	2 审核通过\n	3 驳回',
  `operator` int(11) NOT NULL COMMENT '审核用户ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='仓库';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `warehouse_employee`
--

DROP TABLE IF EXISTS `warehouse_employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_employee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(11) NOT NULL COMMENT '仓库ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `role_id` int(11) DEFAULT NULL COMMENT '权限组ID',
  `created_at` int(11) NOT NULL COMMENT '添加时间',
  `operator` int(11) NOT NULL COMMENT '操作人用户ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='仓库员工';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50112 SET @disable_bulk_load = IF (@is_rocksdb_supported, 'SET SESSION rocksdb_bulk_load = @old_rocksdb_bulk_load', 'SET @dummy_rocksdb_bulk_load = 0') */;
/*!50112 PREPARE s FROM @disable_bulk_load */;
/*!50112 EXECUTE s */;
/*!50112 DEALLOCATE PREPARE s */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-03-27 15:51:24
