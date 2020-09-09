<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//帐单类型

class CreateAccountTypeTable extends Migration
{
    /**
     * 
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_type', function (Blueprint $table)  {     
            $table->increments('id'); 
            $table->integer('warehouse_id')->comment('仓库ID');
            $table->tinyInteger('is_income')->comment('是否为收入');
            $table->string('name_cn')->comment('帐目名称');
            $table->integer('user_id')->comment('经手人');
            $table->string('remark')->comment('备注');

            $table->index(['warehouse_id']);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_type');
    }
}
