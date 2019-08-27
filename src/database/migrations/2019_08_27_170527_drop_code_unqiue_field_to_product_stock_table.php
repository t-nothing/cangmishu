<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropCodeUnqiueFieldToProductStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product_stock', function (Blueprint $table)  {                 
            $table->dropUnique('product_stock_sku_unique');
            $table->unique(['warehouse_id', 'sku']); //仓库个人代码唯一就行了
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
