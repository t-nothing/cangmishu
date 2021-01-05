<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStockNumToProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product', function (Blueprint $table)  {
            $table->integer('total_stockin_num')->comment('总入库库存')->default(0);            
            $table->integer('total_shelf_num')->comment('总上架库存')->default(0);           
            $table->integer('total_floor_num')->comment('总未上架库存')->default(0);         
            $table->integer('total_lock_num')->comment('总锁定库存')->default(0);
            $table->integer('total_stockout_num')->comment('总出库库存')->default(0);
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
            $table->dropColumn('total_stockin_num');
            $table->dropColumn('total_stockout_num');
            $table->dropColumn('total_lock_num');
            $table->dropColumn('total_shelf_num');
            $table->dropColumn('total_floor_num');
        });
    }
}
