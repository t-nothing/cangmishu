<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsUserCertificationOwnerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_certification_owner', function (Blueprint $table) {
            $table->string('warehouse_property',255)->comment('仓库产权方');
            $table->string('phone_codes',255)->comment('电话区号');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_certification_owner', function (Blueprint $table) {
            //
        });
    }
}
