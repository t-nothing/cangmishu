<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToProductStockLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product_stock_log', function (Blueprint $table)  {         
            $table->string('source', 10)->comment('来源')->default('web');       
            $table->integer('product_total_stockin_num')->comment('商品总库存')->default(0);       
            // $table->integer('spec_total_stockin_num')->comment('规格总库存')->default(0);         
            $table->integer('stock_total_stockin_num')->comment('SKU总库存')->default(0);
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
            $table->dropColumn('source');
            $table->dropColumn('product_total_stockin_num');
            // $table->dropColumn('spec_total_stockin_num');
            $table->dropColumn('stock_total_stockin_num');
        });
    }
}
