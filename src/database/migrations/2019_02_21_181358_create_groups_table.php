<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->string("name")->comment("用户分组名称");
            $table->integer("user_id")->comment("创建分组的用户id");
            $table->integer("warehouse_id")->comment("分组所在的仓库");
            $table->integer("role_id")->comment("分组的角色");
            $table->string("remark")->comment("备注");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
    }
}
