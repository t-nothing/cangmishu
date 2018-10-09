<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOldShelfNumToProductStockLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_stock_log', function (Blueprint $table) {
            $table->integer('sku_total_shelf_num_old')->default(0)->after('spec_total_shelf_num')->comment('原库存');
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
