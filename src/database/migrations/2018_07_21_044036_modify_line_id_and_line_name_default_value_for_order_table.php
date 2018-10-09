<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyLineIdAndLineNameDefaultValueForOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order', function (Blueprint $table) {
            $table->integer('status')->nullable(false)->default(1)->change();
            $table->string('line_id')->nullable(false)->default('')->change();
            $table->string('line_name')->nullable(false)->default('')->change();
            $table->string('remark')->nullable(false)->default('')->change();
            $table->string('shop_remark')->nullable(false)->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order', function (Blueprint $table) {
            //
        });
    }
}
