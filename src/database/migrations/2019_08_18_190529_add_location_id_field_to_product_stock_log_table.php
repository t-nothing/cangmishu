<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationIdFieldToProductStockLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product_stock_log', function (Blueprint $table)  {                 
            $table->integer('product_stock_location_id')->comment('库存货位ID')->default(0);
            $table->string('product_stock_location_code')->comment('库存货位代码')->default('0');
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
            $table->dropColumn('product_stock_location_id');
            $table->dropColumn('product_stock_location_code');
        });
    }
}
