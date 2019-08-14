<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductStockLocationIdToMergePickTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merge_pick', function (Blueprint $table) {
            $table->integer('product_stock_location_id')->comment('拣货库存货位ID');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('merge_pick', function (Blueprint $table) {
            $table->dropColumn('product_stock_location_id');
        });
    }
}
