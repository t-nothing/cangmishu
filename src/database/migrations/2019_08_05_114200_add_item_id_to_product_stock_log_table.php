<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class addItemIdToProductStockLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_stock_log', function (Blueprint $table) {
            $table->integer('item_id')->comment('订单明细ID')->default(0);
            $table->index(['spec_id', 'product_stock_id','item_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_stock_log', function (Blueprint $table) {
            $table->dropColumn('item_id');
            $table->dropIndex(['spec_id', 'product_stock_id','item_id']);
        });
    }
}
