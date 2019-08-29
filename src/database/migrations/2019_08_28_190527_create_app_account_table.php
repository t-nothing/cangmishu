<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class CreateAppAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('app_account', function (Blueprint $table)  {
            $table->increments('id'); 
            $table->string('app_key')->comment('APP KEY');
            $table->string('app_secret')->comment('APP SECRET');
            $table->unsignedInteger('owner_id')->comment('用户id');
            $table->unsignedInteger('warehouse_id')->comment('仓库ID');
            $table->tinyInteger('is_enabled')->comment('是否启用')->default(1);
            $table->tinyInteger('is_enabled_push')->comment('是否推送')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_account');
    }
}
