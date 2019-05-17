<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsOnUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            // ALTER TABLE `user` CHANGE `created_at` `created_at` INT(11) NULL DEFAULT NULL;
            $table->integer('created_at')->nullable()->change();
            // ALTER TABLE `user` CHANGE `updated_at` `updated_at` INT(11) NULL DEFAULT NULL;
            $table->integer('updated_at')->nullable()->change();
            // ALTER TABLE `user` ADD UNIQUE(`name`);
            $table->unique('name');
            // ALTER TABLE `user` ADD UNIQUE(`email`);
            $table->unique('email');
            // ALTER TABLE `user` CHANGE `password_digest` `password` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '密码hash';
            $table->renameColumn('password_digest', 'password');
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
