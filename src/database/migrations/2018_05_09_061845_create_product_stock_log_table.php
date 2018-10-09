<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductStockLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_stock_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type_id')->default(1);
            $table->string('order_sn')->comment('出入库单号');
            $table->integer('owner_id')->default(0)->comment('商家');
            $table->integer('warehouse_id')->default(0)->comment('仓库');
            $table->integer('spec_id')->default(0);
            $table->string('sku');
            $table->integer('num')->default(0)->comment('操作数量');
            $table->integer('operator')->default(0)->comment('操作人');
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_stock_log');
    }
}
