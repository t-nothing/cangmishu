<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupRelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_rel', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer("user_id")->comment('用户id');
            $table->integer("group_id")->comment('分组id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_rel');
    }
}
