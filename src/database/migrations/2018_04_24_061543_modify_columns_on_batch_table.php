<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsOnBatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batch', function (Blueprint $table) {
            $table->string('confirmation_number', 255)->comment('确认单编号');
            $table->tinyInteger('ship_type')->default(0)->comment('运输方式')->nullable();
            //$table->string('confirmation_number', 255)->comment('运单号')->nullable();   报错
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
