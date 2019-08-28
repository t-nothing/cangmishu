<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class CreateAppOutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('app_out', function (Blueprint $table)  {
            $table->increments('id'); 
            $table->unsignedInteger('app_in_id')->comment('对应的KEY');
            $table->unsignedTinyInteger('type_id')->comment('1.平台,2.物流');
            $table->string('app_key')->comment('APP KEY');
            $table->string('app_secret')->comment('APP SECRET');
            $table->unsignedInteger('owner_id')->comment('用户id');
            $table->unsignedInteger('warehouse_id')->comment('仓库ID');
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
        Schema::dropIfExists('app_out');
    }
}
