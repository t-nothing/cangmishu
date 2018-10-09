<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCategoryWarningTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_category_warning', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->integer('user_id')->comment('所属user_id');
            $table->integer('category_id')->comment('所属category_id');
            $table->integer('warning_stock')->default(0)->comment('最低库存报警值');
            $table->integer('created_at')->nullable()->comment('创建时间');
            $table->integer('updated_at')->nullable()->comment('修改时间');
        });
        Schema::table('user', function (Blueprint $table) {
            $table->integer('default_warning_stock')->default(0)->comment('默认最低库存报警值');
            $table->string('warning_email', 255)->nullable()->comment('报警邮箱');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_category_warning');
    }
}
