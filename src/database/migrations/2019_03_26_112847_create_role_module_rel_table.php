<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleModuleRelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_module_rel',function (Blueprint $table) {
            $table->increments('id');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer("role_id")->comment("角色：产权方为2,租赁房为3");
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
        Schema::table('role_module_rel', function (Blueprint $table) {
            //
        });
    }
}
