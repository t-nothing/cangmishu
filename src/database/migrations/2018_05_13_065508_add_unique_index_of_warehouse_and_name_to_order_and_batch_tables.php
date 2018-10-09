<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexOfWarehouseAndNameToOrderAndBatchTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batch_type', function (Blueprint $table) {
            $table->dropUnique('batch_type_name_unique');
            $table->unique(['warehouse_id', 'name']);
        });

        Schema::table('order_type', function (Blueprint $table) {
            $table->dropUnique('order_type_name_unique');
            $table->unique(['warehouse_id', 'name']);
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
