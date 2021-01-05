<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class RenameStockFieldForProductStockLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product_stock_log', function (Blueprint $table)  {                 
            $table->renameColumn('product_total_stockin_num', 'product_total_stock_num');  
            $table->renameColumn('spec_total_stockin_num', 'spec_total_stock_num');  
            $table->renameColumn('stock_total_stockin_num', 'stock_total_stock_num');  
            $table->dropColumn('sku_total_shelf_num_old');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('product', function (Blueprint $table) {
            $table->renameColumn('product_total_stock_num', 'product_total_stockin_num');  
            $table->renameColumn( 'spec_total_stock_num','spec_total_stockin_num');  
            $table->renameColumn('stock_total_stock_num','stock_total_stockin_num');  

        });
    }
}
