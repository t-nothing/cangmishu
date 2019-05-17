<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductStockLock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_stock_lock', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->integer('relevance_code')->comment('对应商品的外部编码');
            $table->integer('stock_id')->comment('库存sku的id');
            $table->integer('order_id')->comment('对应订单的id');
            $table->integer('order_item_id')->comment('对应订单的商品的id');
            $table->integer('lock_amount')->comment('锁定了多少个库存');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->nullable()->comment('修改时间');
            $table->integer('over_time')->nullable()->comment('拣货时间,也就是库存解除锁定的时间');
            $table->unique(["order_item_id","stock_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_stock_lock');
    }
}
