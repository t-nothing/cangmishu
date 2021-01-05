<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalStockNumFieldToProductSpecTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product_spec', function (Blueprint $table)  {                 
            $table->integer('total_stock_num')->comment('总库存')->default(0);  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('product_spec', function (Blueprint $table) {
            $table->dropColumn('total_stock_num');
        });
    }
}
