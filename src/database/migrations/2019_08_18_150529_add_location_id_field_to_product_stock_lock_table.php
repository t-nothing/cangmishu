<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationIdFieldToProductStockLockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product_stock_lock', function (Blueprint $table)  {                 
            $table->integer('product_stock_location_id')->comment('锁定库存货位')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('product_stock_lock', function (Blueprint $table) {
            $table->dropColumn('product_stock_location_id');
        });
    }
}
