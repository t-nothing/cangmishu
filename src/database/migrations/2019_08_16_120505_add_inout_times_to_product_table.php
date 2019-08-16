<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInoutTimesToProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('product', function (Blueprint $table)  {                 
            $table->integer('total_stockin_times')->comment('入库次数')->default(0);  
            $table->integer('total_stockout_times')->comment('出库次数')->default(0);  
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
            $table->dropColumn('total_stockin_times');
            $table->dropColumn('total_stockout_times');
        });
    }
}
