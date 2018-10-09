<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimestampForUserAppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_app', function (Blueprint $table) {
            $table->integer('bind_user_id')->nullable(false)->change();
            $table->integer('created_at')->nullable()->change();
            $table->integer('updated_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_app', function (Blueprint $table) {
            //
        });
    }
}
