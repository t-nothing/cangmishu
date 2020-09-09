<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//收款单
class CreateDebitNoteTable extends Migration
{
    /**
     * 
     *
     * @return void
     */
    public function up()
    {
        Schema::create('debit_note', function (Blueprint $table)  {     
            $table->increments('id'); 
            $table->integer('warehouse_id')->comment('仓库ID');
            $table->string('out_sn')->comment('单据编号');
            $table->integer('client_id')->comment('客户ID');
            $table->integer('user_id')->comment('经手人');
            $table->decimal('sub_total',10,2)->comment('应收款项');
            $table->decimal('sub_paid',10,2)->comment('本次收款金额');
            $table->decimal('sub_discount_fee',10,2)->comment('本次优惠');
            $table->json('attache_files')->comment('图片附件');

            $table->index(['warehouse_id']);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('debit_note');
    }
}
