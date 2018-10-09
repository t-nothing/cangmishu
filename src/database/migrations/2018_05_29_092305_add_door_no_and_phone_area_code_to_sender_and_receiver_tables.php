<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDoorNoAndPhoneAreaCodeToSenderAndReceiverTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_sender', function (Blueprint $table) {
            $table->string('door_no')->default('')->after('city');
            $table->string('phone_area_code')->default('')->after('fullname');
        });

        Schema::table('user_receiver', function (Blueprint $table) {
            $table->string('door_no')->default('')->after('city');
            $table->string('phone_area_code')->default('')->after('fullname');
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
