<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexRelatedToWarehouse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category', function (Blueprint $table) {
            $table->dropUnique(['owner_id', 'name_cn']);
            $table->dropUnique(['owner_id', 'name_en']);
            $table->unique(['warehouse_id', 'name_cn']);
            $table->unique(['warehouse_id', 'name_en']);
        });
        Schema::table('order_type', function (Blueprint $table) {
            $table->dropUnique(['owner_id', 'name']);
            $table->unique(['warehouse_id', 'name']);
        });
        Schema::table('batch_type', function (Blueprint $table) {
            $table->dropUnique(['owner_id', 'name']);
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
