<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class AddAddressToDistributorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('distributor', function (Blueprint $table)  {  
            $table->string('address')->comment('详细地址')->default(""); 
            $table->string('province',50)->comment('省')->default(""); 
            $table->string('district',50)->comment('区')->default(""); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor', function (Blueprint $table)  {  
            $table->dropColumn('address');
            $table->dropColumn('province');
            $table->dropColumn('district');
        });
    }
}
