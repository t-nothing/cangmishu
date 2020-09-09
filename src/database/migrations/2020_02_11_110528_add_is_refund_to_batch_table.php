<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class AddIsRefundToBatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('batch', function (Blueprint $table)  {  
            $table->boolean('is_refund')->comment('是否退货单')->default(0);    
            $table->integer('refund_order_id')->comment('退货订单ID')->default(0);               
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('batch', function (Blueprint $table)  {  
            $table->dropColumn('is_refund');
            $table->dropColumn('refund_order_id'); 
        });
    }
}
