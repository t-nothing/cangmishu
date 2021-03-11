<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class AddLocatonCodeToBatchProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('batch_product', function (Blueprint $table)  {  
            $table->string('location_code')->comment('推荐货位,导入用')->default(""); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('batch_product', function (Blueprint $table)  {  
            $table->dropColumn('location_code');
        });
    }
}
