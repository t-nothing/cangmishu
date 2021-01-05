<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_user', function (Blueprint $table) {
            $table->increments('id');

            $table->string('nick_name')->comment('用户昵称');
            $table->string('avatar_url')->comment('用户头像图片的 UR')->nullable();
            $table->tinyInteger('gender')->comment('用户性别')->nullable();
            $table->string('country')->comment('用户所在国家')->nullable();
            $table->string('province')->comment('用户所在省份')->nullable();
            $table->string('city')->comment('用户所在城市')->nullable();
            $table->string('mobile')->comment('手机号')->nullable();
            $table->string('language')->comment('语言');
            $table->string('openid')->comment('OPEN ID');
            $table->string('last_login_ip')->comment('最后登录IP');
            $table->integer('last_login_time')->comment('最后登录时间');

            $table->string('weapp_openid')->nullable()->comment('微信开放id');
            $table->string('weapp_session_key')->nullable()->comment('微信session_key');

            $table->string('password');
            $table->rememberToken();

            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer('deleted_at')->comment('删除时间')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_user');
    }
}
