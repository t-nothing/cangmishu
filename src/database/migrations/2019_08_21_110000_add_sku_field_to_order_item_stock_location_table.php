<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AddSkuFieldToOrderItemStockLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('order_item_stock_location', function (Blueprint $table)  { $table->string('relevance_code')->comment('外部编码');
            $table->string('stock_sku')->comment('入库批次号');
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
            $table->dropColumn('relevance_code');
            $table->dropColumn('stock_sku');

        });
    }
}
