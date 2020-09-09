<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//收款单详细
class CreateDebitNoteListTable extends Migration
{
    /**
     * 
     *
     * @return void
     */
    public function up()
    {
        Schema::create('debit_note_list', function (Blueprint $table)  {     
            $table->increments('id'); 
            $table->integer('warehouse_id')->comment('仓库ID');
            $table->integer('debit_note_id')->comment('帐单ID');
            $table->integer('checkout_account_id')->comment('结算帐户');
            $table->decimal('total',10,2)->comment('应收款项');
            $table->decimal('paid',10,2)->comment('本次收款金额');
            $table->decimal('discount_fee',10,2)->comment('本次优惠');
            $table->string('remark')->comment('备注');

            $table->index(['warehouse_id', 'debit_note_id']);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('debit_note_list');
    }
}
