<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class AddIsRefundToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('order', function (Blueprint $table)  {  
            $table->boolean('is_refund')->comment('是否退款')->default(0);    
            $table->integer('refund_at')->comment('退款时间')->default(0);               
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order', function (Blueprint $table)  {  
            $table->dropColumn('is_refund');
            $table->dropColumn('refund_at'); 
        });
    }
}
