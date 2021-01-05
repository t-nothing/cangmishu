<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpecNameFieldToOrderItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('order_item', function (Blueprint $table)  { 
            $table->string('spec_name_cn')->comment('规格名称')->nullable();
            $table->string('spec_name_en')->comment('规格名称')->nullable();
            $table->string('pic')->comment('商品名称')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('order_item', function (Blueprint $table) {
            $table->dropColumn('spec_name_cn');            
            $table->dropColumn('spec_name_en');            
            $table->dropColumn('pic'); 
        });
    }
}
