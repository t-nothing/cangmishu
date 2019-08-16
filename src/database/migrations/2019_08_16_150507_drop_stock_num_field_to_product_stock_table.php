<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropStockNumFieldToProductStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product_stock', function (Blueprint $table)  {                 
            $table->dropColumn('total_stockin_num');  
            $table->dropColumn('total_shelf_num');  
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
            $table->integer('total_stockin_num')->nullable()->comment('入库总库存');
            $table->integer('total_shelf_num')->nullable()->comment('上架总库存');
        });
    }
}
