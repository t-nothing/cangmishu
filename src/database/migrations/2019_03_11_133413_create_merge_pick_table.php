<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMergePickTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merge_pick', function (Blueprint $table) {
            $table->increments('id');
            $table->string('merge_pick_num')->comment('拣货单号');
            $table->string('shipment_num')->comment('关联的拣货单号');
            $table->integer('order_id')->comment('关联的订单id');
            $table->integer('order_item_id')->comment('关联的商品id');
            $table->integer('order_item_num')->comment('关联的商品数量');
            $table->integer('product_stock_id')->comment('关联的库存id');
            $table->integer('pick_num')->comment('订单商品数量');
            $table->string('location')->comment('库存的货位');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merge_pick');
    }
}
