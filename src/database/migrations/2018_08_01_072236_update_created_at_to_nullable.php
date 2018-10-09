<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCreatedAtToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batch_type', function (Blueprint $table) {
            $table->integer('created_at')->nullable()->change();
        });

        Schema::table('distributor', function (Blueprint $table) {
            $table->integer('created_at')->nullable()->change();
        });

        Schema::table('order', function (Blueprint $table) {
            $table->integer('created_at')->nullable()->change();
        });

        Schema::table('order_item', function (Blueprint $table) {
            $table->integer('created_at')->nullable()->change();
        });

        Schema::table('order_type', function (Blueprint $table) {
            $table->integer('created_at')->nullable()->change();
        });

        Schema::table('warehouse', function (Blueprint $table) {
            $table->integer('created_at')->nullable()->change();
        });

        Schema::table('warehouse_location', function (Blueprint $table) {
            $table->integer('created_at')->nullable()->change();
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
