<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdateAtToSkuAndBatchMarkLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('sku_mark_log', function (Blueprint $table) {
		    $table->integer('updated_at')->comment('修改时间');
            
	    });
	    Schema::table('batch_mark_log', function (Blueprint $table) {
	     $table->integer('updated_at')->comment('修改时间');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sku_mark_log', function (Blueprint $table) {
            //
        });
    }
}
