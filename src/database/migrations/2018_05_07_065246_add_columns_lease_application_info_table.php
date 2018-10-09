<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsLeaseApplicationInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lease_application_info', function (Blueprint $table) {
            //仓库名字
            $table->string('warehouse_name', 100)->comment('仓库名字');
            //用户id
            $table->integer('owner_id')->comment('申请用户id');
            //审核人
            $table->integer('check_user_id')->comment('审核人ID');
            //审核日期
            $table->integer('check_data')->comment('审核日期');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lease_application_info', function (Blueprint $table) {
            //
        });
    }
}
