<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CareteSkuMarkLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sku_mark_log', function (Blueprint $table) {
	   $table->engine = 'InnoDB';
	   $table->charset = 'utf8';
	   $table->collation = 'utf8_general_ci';

	   $table->increments('id');
	   $table->string('warehouse_code');
	   $table->integer('spec_id');
	   $table->integer('sku_mark'); 
	   $table->integer('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sku_mark_log');
    }
}
