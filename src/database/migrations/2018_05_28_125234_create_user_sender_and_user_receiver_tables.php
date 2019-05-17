<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSenderAndUserReceiverTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_sender', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->tinyInteger('is_default')->default(0);
            $table->integer('user_id');
            $table->string('country');
            $table->string('province');
            $table->string('city');
            $table->string('address');
            $table->string('postcode');
            $table->string('fullname');
            $table->string('phone');
            $table->string('email')->default('');
            $table->string('company')->default('');
            $table->string('remark')->default('');
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
        });

        Schema::create('user_receiver', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->tinyInteger('is_default')->default(0);
            $table->integer('user_id');
            $table->string('country');
            $table->string('province');
            $table->string('city');
            $table->string('address');
            $table->string('postcode');
            $table->string('fullname');
            $table->string('phone');
            $table->string('email')->default('');
            $table->string('company')->default('');
            $table->string('remark')->default('');
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_sender');
        Schema::dropIfExists('user_receiver');
    }
}
