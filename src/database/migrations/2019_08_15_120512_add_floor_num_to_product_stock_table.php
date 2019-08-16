<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFloorNumToProductStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product_stock', function (Blueprint $table)  {         
            $table->integer('floor_num')->comment('未上架库存')->default(0);         
            $table->integer('lock_num')->comment('锁定库存')->default(0);
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
            $table->dropColumn('floor_num');
            $table->dropColumn('lock_num');
        });
    }
}
