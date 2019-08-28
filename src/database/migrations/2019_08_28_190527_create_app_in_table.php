<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class CreateAppInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('app_in', function (Blueprint $table)  {
            $table->increments('id'); 
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
        Schema::dropIfExists('app_in');
    }
}
