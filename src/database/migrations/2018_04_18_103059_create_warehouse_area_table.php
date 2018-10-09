<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * CREATE TABLE `waregouse_area` (

        `id` int(11) NOT NULL COMMENT '编号',
        `code` varchar(50) NOT NULL COMMENT '货区编号',
        `warehouse_id` int(11) NOT NULL COMMENT '所属仓库ID',
        `name` varchar(50) NOT NULL COMMENT '货区名称',

        `temperature` tinyint(1) NOT NULL COMMENT '储存温度',

        `is_enabled` tinyint(1) NOT NULL COMMENT '启用状态 1常温 2冷藏',

        `functions` varchar(255) NOT NULL COMMENT '功能分类',

        `remark` varchar(255) NOT NULL COMMENT '备注',

        `updated_at` int(11) NOT NULL,

        `created_at` int(11) NOT NULL
        )
        ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='货区信息'
        ALTER TABLE `waregouse_area` ADD `deleted_at` INT(11) NOT NULL AFTER `created_at`;
         */
        Schema::create('warehouse_area', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->string('code', 50)->comment('货区编号');
            $table->integer('warehouse_id')->comment('所属仓库ID');
            $table->tinyInteger('temperature')->comment('储藏温度 1常温 2冷藏 3 冷冻');
            $table->tinyInteger('is_enabled')->comment('开启状态 1开启 2 未开启');
            $table->string('functions')->comment('功能分类');
            $table->string('remark')->comment('备注');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->nullable()->comment('修改时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_area');
    }
}
