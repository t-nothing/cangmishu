<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToShelfTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shelf', function (Blueprint $table) {
            $table->float('capacity')->after('name');
            $table->integer('warehouse_area_id')->after('warehouse_id');
            $table->string('passage', 10)->nullable();
            $table->string('row', 10)->nullable();
            $table->string('col', 10)->nullable();
            $table->string('floor', 10)->nullable();
            $table->tinyInteger('is_enabled')->default(1);
            $table->string('remark')->nullable();
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
