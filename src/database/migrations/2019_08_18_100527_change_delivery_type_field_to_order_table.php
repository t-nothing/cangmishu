<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDeliveryTypeFieldToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('order', function (Blueprint $table)  {                 
            $table->boolean('delivery_type')->change()->default(1)->comment('1, 1为空运, 2.为海运, 3为陆运');  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('order', function (Blueprint $table) {
            $table->dropColumn('delivery_type');
        });
    }
}
