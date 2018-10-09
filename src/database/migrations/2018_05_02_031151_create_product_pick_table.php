<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductPickTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_pick', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->integer('product_stock_id')->comment('对应product_stock表id');
            $table->integer('created_at')->nullable()->comment('创建时间');
            $table->integer('updated_at')->nullable()->comment('更新时间');
            $table->integer('status')->comment('状态');
            $table->integer('num')->comment('需要捡货数量');
            $table->integer('pick_num')->default(0)->comment('实际捡货数量');
            $table->integer('old_num')->comment('捡货前商品上架数量');
            $table->integer('new_num')->comment('捡货后商品上架数量');
            $table->string('shipment_num','255')->comment('捡货单号');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_pick');
    }
}
