<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceFieldToProductStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product_stock', function (Blueprint $table)  {                 
            $table->decimal('purchase_price', 10, 2)->comment('采购价格')->default(0);
            $table->string('purchase_currency')->nullable()->comment('采购货币');
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
            $table->dropColumn('purchase_price');
            $table->dropColumn('purchase_currency');
        });
    }
}
