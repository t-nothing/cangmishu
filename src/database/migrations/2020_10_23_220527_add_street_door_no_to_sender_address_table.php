<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class AddStreetDoorNoToSenderAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('sender_address', function (Blueprint $table)  {  
            $table->string('street')->comment('街道')->default(""); 
            $table->string('door_no')->comment('门牌号')->default(""); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sender_address', function (Blueprint $table)  {  
            $table->dropColumn('street');
            $table->dropColumn('door_no');
        });
    }
}
