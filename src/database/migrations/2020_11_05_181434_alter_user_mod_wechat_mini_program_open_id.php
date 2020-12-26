<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserModWechatMiniProgramOpenId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('wechat_mini_program_open_id')
                ->nullable()
                ->default(null)
                ->comment('微信小程序OPENID')
                ->change();
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
            $table->string('wechat_mini_program_open_id')
                ->default('')
                ->comment('微信小程序OPENID')
                ->change();
        });
    }
}
