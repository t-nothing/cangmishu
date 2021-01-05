<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class AddWechatMpOpenidTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('user', function (Blueprint $table)  {  
            $table->string('wechat_mini_program_open_id')->comment('微信小程序OPENID')->default("");
            $table->index('wechat_mini_program_open_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table)  {  
            $table->dropColumn('wechat_mini_program_open_id');
            $table->dropIndex('wechat_mini_program_open_id');
        });
    }
}
