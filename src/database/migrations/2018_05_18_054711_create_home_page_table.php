<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('home_page_analyze', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->integer('warehouse_id')->comment('所属warehouse_id');
            $table->string('warehouse_name', 255)->nullable()->comment('所属仓库名称');
            $table->integer('batch_count')->default(0)->comment('入库次数');
            $table->integer('order_count')->default(0)->comment('出库次数');
            $table->integer('batch_product_num')->default(0)->comment('入库商品数量');
            $table->integer('order_product_num')->default(0)->comment('出库商品数量');
            $table->integer('product_total')->default(0)->comment('商品库存');
            $table->integer('record_time')->nullable()->comment('记录时间');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->nullable()->comment('修改时间');
        });

        Schema::create('home_page_notice', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->integer('notice_time')->comment('通知时间');
            $table->string('notice_type', 255)->comment('操作类型');
            $table->string('notice_warehouse', 255)->comment('通知仓库');
            $table->integer('owner_id')->comment('所属user_id');
            $table->integer('notice_relation_id')->comment('关联id');
            $table->boolean('notice_status')->comment('数据状态');
            $table->boolean('status')->comment('显示状态');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->nullable()->comment('修改时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
            $table->string('notice_info', 255)->comment('通知信息');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('home_page_analyze');
        Schema::dropIfExists('home_page_notice');
    }
}
