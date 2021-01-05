<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AddVerifyNumToOrderItemStockLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_item_stock_location', function (Blueprint $table) {
            $table->integer('verify_num')->comment('验货数量');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_item_stock_location', function (Blueprint $table) {
            $table->dropColumn('verify_num');
        });
    }
}
