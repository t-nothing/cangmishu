<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStockNumFieldToProductStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product_stock', function (Blueprint $table)  {                 
            $table->integer('stock_num')->comment('总库存')->default(0);  
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
            $table->dropColumn('stock_num');
        });
    }
}
