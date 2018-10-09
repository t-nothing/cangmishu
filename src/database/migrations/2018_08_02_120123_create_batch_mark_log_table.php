<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchMarkLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_mark_log', function (Blueprint $table) {

	$table->engine = 'InnoDB'; 
        $table->charset = 'utf8';                                                                                            $table->collation = 'utf8_general_ci';  

        $table->increments('id');
	$table->string('warehouse_code');
	$table->integer('batch_mark');
	$table->integer('created_at')->comment('创建时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_mark_log');
    }
}
