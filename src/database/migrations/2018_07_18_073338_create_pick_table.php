<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePickTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pick', function (Blueprint $table) {
            $table->increments('id');
            $table->string('shipment_num');
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();

            $table->unique('shipment_num');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pick');
    }
}
