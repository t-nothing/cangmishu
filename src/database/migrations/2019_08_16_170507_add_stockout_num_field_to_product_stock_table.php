<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStockOutNumFieldToProductStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product_stock', function (Blueprint $table)  {                 
            $table->integer('stockout_num')->comment('出库数量')->default(0);  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('product_stock', function (Blueprint $table) {
            $table->dropColumn('stockout_num');
        });
    }
}
