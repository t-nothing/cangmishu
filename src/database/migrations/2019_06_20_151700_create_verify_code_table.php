<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerifyCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verify_code', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->comment('邮箱');
            $table->string('code')->comment("验证码");
            $table->integer('expired_at')->comment("过期时间");
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('verify_code', function (Blueprint $table) {
            //
        });
    }
}
