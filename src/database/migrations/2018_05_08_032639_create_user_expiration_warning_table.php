<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserExpirationWarningTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_expiration_warning', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->integer('user_id')->comment('所属user_id');
            $table->integer('category_id')->comment('所属category_id');
            $table->integer('warning_expiration')->default(0)->comment('最低保质期到期/天报警');
            $table->integer('created_at')->nullable()->comment('创建时间');
            $table->integer('updated_at')->nullable()->comment('修改时间');
        });

        Schema::table('user', function (Blueprint $table) {
            $table->integer('default_warning_expiration')->default(0)->comment('默认最低保质期到期报警天数');
            $table->string('warning_expiration_email', 255)->nullable()->comment('保质期到期报警邮箱');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_expiration_warning');
    }
}
