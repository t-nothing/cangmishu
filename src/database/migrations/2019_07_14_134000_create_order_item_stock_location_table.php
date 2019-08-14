<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemStockLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_item_stock_location', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_id')->comment('订单商品ID');
            $table->integer('stock_id')->comment('库存ID');
            $table->integer('pick_num')->comment('拣货数量');
            $table->integer('product_stock_location_id')->comment('库存位置ID');
            $table->integer('warehouse_location_id')->comment('仓库货位ID');
            $table->string('warehouse_location_code')->comment('仓库货位编码');
            $table->integer('warehouse_id')->comment('仓库ID');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->index('item_id');
            $table->index('warehouse_location_id');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_item_stock_location');
    }
}
