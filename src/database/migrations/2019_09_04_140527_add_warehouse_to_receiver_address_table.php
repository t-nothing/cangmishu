<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class AddWarehouseToReceiverAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receiver_address', function (Blueprint $table)  {     
            $table->integer('warehouse_id')->comment('仓库ID')->nullable()->default(0);          
        });

        Schema::table('sender_address', function (Blueprint $table)  {     
            $table->integer('warehouse_id')->comment('仓库ID')->nullable()->default(0);          
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receiver_address', function (Blueprint $table)  { 
            $table->dropColumn('warehouse_id');                 
        });

        Schema::table('sender_address', function (Blueprint $table)  { 
            $table->dropColumn('warehouse_id');                 
        });
    }
}
