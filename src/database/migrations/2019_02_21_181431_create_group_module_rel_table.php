<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupModuleRelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_module_rel', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer("group_id")->comment("用户分组id");
            $table->integer("module_id")->comment("模块id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_module_rel');
    }
}
