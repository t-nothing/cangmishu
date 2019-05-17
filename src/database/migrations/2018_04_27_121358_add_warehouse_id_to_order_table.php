<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWarehouseIdToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order', function (Blueprint $table) {
            $table->integer('warehouse_id')->nullable()->comment('仓库编号');
            $table->string('shipment_num')->nullable()->comment('打包单号');
            $table->string('line_name')->nullable()->comment('路线');
            $table->string('verify_status')->nullable()->comment('验货状态：1未验货 2 已验货 3验货有误');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order', function (Blueprint $table) {
            //
        });
    }
}
