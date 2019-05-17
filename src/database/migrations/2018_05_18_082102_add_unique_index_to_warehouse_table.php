<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexToWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "ALTER TABLE `warehouse` CHANGE `name_cn` `name_cn` VARCHAR(255);
                ALTER TABLE `warehouse` CHANGE `name_en` `name_en` VARCHAR(255) AFTER `name_cn`;";

        DB::unprepared($sql);

        Schema::table('warehouse', function (Blueprint $table) {
            $table->unique('name_cn');
            $table->unique('name_en');
            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouse', function (Blueprint $table) {
            //
        });
    }
}
