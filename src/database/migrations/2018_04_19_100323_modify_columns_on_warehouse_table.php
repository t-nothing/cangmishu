<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsOnWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::table('user', function (Blueprint $table) {
        Schema::table('warehouse', function (Blueprint $table) {
            //ALTER TABLE `warehouse` ADD `temperature` INT(11) NOT NULL COMMENT '储存温度 1 常温 2 冷藏 3 冷冻' AFTER `type`;
            $table->tinyInteger('temperature')->default(1)->comment('储存温度 1常温 2 冷藏 3 冷冻 4 不限');
            //ALTER TABLE `warehouse` ADD `area` VARCHAR(50) NOT NULL COMMENT '仓库面积' AFTER `code`;
            $table->string('area')->comment('仓库面积');
            //ALTER TABLE `warehouse` ADD `contact_user` VARCHAR(50) NOT NULL COMMENT '联系人' AFTER `postcode`,
            $table->string('contact_user', '50')->comment('联系人');
            //ADD `contact_number` VARCHAR(50) NOT NULL COMMENT '联系电话' AFTER `contact_user`,
            $table->string('contact_number', '50')->comment('联系电话');
            //ADD `contact_email` VARCHAR(255) NOT NULL COMMENT '联系邮件' AFTER `contact_number`;
            $table->string('contact_email', '255')->comment('联系邮件');
            //ALTER TABLE `warehouse` ADD `is_use` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '是否使用 1 是 2 否' AFTER `status`;
            $table->tinyInteger('is_use')->default(1)->comment('是否使用 1 是 2 否');
            //ALTER TABLE `warehouse` ADD `apply_num` INT(11) NOT NULL DEFAULT '0' COMMENT '申请数' AFTER `is_use`;
            $table->integer('apply_num')->default(1)->comment('申请数');
            //ALTER TABLE `warehouse` CHANGE `name` `name_cn` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '仓库中文名称';
            $table->renameColumn('name', 'name_cn');
            //ALTER TABLE `warehouse` ADD `name_en` VARCHAR(200) NOT NULL COMMENT '仓库英文名' AFTER `name_cn`;
            $table->string('name_en', '50');
            $table->string('logo_photo', '255');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('warehouse');
    }
}
