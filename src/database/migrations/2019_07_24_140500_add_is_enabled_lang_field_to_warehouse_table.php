<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsEnabledLangFieldToWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('warehouse', function (Blueprint $table)  {
            $table->boolean('is_enabled_lang')->comment('是否开启双语')->default(0);
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
            $table->dropColumn('is_enabled_lang');
        });
    }
}
