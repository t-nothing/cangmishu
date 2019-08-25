<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class AddQtyFieldToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('order', function (Blueprint $table)  {                 
            $table->integer('sub_pick_num')->comment('拣货数量')->default(0);
            $table->integer('sub_order_qty')->comment('下单数量')->default(0);
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
