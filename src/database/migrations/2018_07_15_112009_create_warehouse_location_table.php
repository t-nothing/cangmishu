<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_location', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('warehouse_id');
            $table->integer('warehouse_area_id');
            $table->string('code');
            $table->float('capacity');
            $table->boolean('is_enabled');
            $table->string('passage', 15)->default('')->comment('通道');
            $table->string('row', 15)->default('')->comment('排');
            $table->string('col', 15)->default('')->comment('列');
            $table->string('floor', 15)->default('')->comment('列');
            $table->string('remark')->default('');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->nullable()->comment('修改时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_location');
    }
}
