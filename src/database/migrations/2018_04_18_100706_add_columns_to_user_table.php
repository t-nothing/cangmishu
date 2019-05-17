<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            // ALTER TABLE `user` ADD `deleted_at` INT(11) NULL DEFAULT NULL AFTER `updated_at`;
            $table->integer('deleted_at')->nullable()->after('updated_at');
            // ALTER TABLE `user` ADD `is_activated` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_locked`;
            $table->tinyInteger('is_activated')->nullable()->after('is_locked');
            // ALTER TABLE `user` ADD `nickname` VARCHAR(256) NOT NULL AFTER `password`;
            $table->string('nickname', 256)->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            //
        });
    }
}
