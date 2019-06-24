<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropIndexWarehouseIdNameUniqueFromBatchTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batch_type', function (Blueprint $table) {
            $table->dropIndex("batch_type_warehouse_id_name_unique");
            $table->unique('owner_id', 'name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('batch_type', function (Blueprint $table) {
            //
        });
    }
}
