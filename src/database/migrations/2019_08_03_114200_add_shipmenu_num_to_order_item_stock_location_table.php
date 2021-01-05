<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AddShipmenuNumToOrderItemStockLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_item_stock_location', function (Blueprint $table) {
            $table->string('shipment_num')->comment('拣货单号');
            $table->index(['shipment_num']); 
            $table->index(['stock_id']); 
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
            $table->dropColumn('shipment_num');
            $table->dropIndex('shipment_num');
            $table->dropIndex('stock_id');
        });
    }
}
