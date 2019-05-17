<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNullableToProductAndChangeAddressToWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->string('hs_code')->nullable()->change();
            $table->string('origin')->nullable()->change();
            $table->string('display_link')->nullable()->change();
        });

        Schema::table('warehouse', function (Blueprint $table) {
            $table->string('province')->nullable();
            $table->string('country')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product', function (Blueprint $table) {
        });
    }
}
