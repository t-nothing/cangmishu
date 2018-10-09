<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpecTotalStockinNumAndSkuTotalStockinNumToProductStockLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_stock_log', function (Blueprint $table) {
            $table->integer('spec_total_stockin_num')->nullable()->comment('货品规格总仓库数量')->after('operation_num');
            $table->integer('sku_total_stockin_num')->nullable()->comment('sku仓库数量')->after('sku_total_shelf_num_old');
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
            //
        });
    }
}
