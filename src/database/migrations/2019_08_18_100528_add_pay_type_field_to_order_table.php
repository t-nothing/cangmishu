<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPayTypeFieldToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('order', function (Blueprint $table)  {                 
            $table->tinyInteger('pay_type')->comment('支付方式')->default(0);  
            $table->string('payment_account_number')->comment('支付帐号信息')->nullable(); 
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
            $table->dropColumn('pay_type');
            $table->dropColumn('payment_account_number');
        });
    }
}
