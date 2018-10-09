<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRentLimitToUserExtraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_extra', function (Blueprint $table) {
            $table->integer('rent_limit')->default(0)->after('share_limit')->comment('租用仓库限制');
        });

        DB::unprepared("
            ALTER TABLE `user_extra` CHANGE `is_certificated_creator` 
                `is_certificated_creator` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否认证仓库创建者';
            ALTER TABLE `user_extra` CHANGE `is_certificated_renter` 
                `is_certificated_renter` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否认证租赁方';
            ALTER TABLE `user_extra` CHANGE `self_use_limit` 
                `self_use_limit` INT(11) NOT NULL DEFAULT '0' COMMENT '自用仓库限制';
            ALTER TABLE `user_extra` CHANGE `share_limit` 
                `share_limit` INT(11) NOT NULL DEFAULT '0' COMMENT '共享仓库限制';
            ALTER TABLE `user_extra` CHANGE `is_auto_verify_self_use` 
                `is_auto_verify_self_use` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否自动审核自用仓库';
            ALTER TABLE `user_extra` CHANGE `is_auto_verify_share` 
                `is_auto_verify_share` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否自动审核共享仓库';
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_extra', function (Blueprint $table) {
            //
        });
    }
}
