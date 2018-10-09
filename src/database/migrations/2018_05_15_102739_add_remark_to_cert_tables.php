<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemarkToCertTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_certification_owner', function (Blueprint $table) {
            $table->integer('check_operator')->default(0);
            $table->string('check_remark')->nullable();
            $table->integer('checked_at')->nullable();
        });

        Schema::table('user_certification_renters', function (Blueprint $table) {
            $table->integer('check_operator')->default(0);
            $table->string('check_remark')->nullable();
            $table->integer('checked_at')->nullable();
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
