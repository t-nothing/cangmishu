<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaseApplicationInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lease_application_info', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->string('user_account')->comment('用户账号（NLE邮箱账号）');
            $table->integer('warehouse_id')->comment('需要开通的仓库id');
            $table->integer('application_data')->comment('申请日期');
            $table->string('application_name')->comment('申请名字');
            $table->integer('application_phone')->comment('申请电话');
            $table->string('application_email')->comment('申请邮箱');
            $table->integer('weekly_shipments')->comment('预计每周发货数量');
            $table->integer('weekly_weight')->comment('预计每周发货重量');
            $table->string('goods_name')->comment('仓库物品名');
            $table->string('sell_country')->comment('销售目的地（国家）');
            $table->string('sales_mode')->comment('销售方式');
            $table->integer('status')->comment('状态');
            $table->integer('created_at')->nullable()->comment('创建时间');
            $table->integer('updated_at')->nullable()->comment('更新时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lease_application_info');
    }
}
