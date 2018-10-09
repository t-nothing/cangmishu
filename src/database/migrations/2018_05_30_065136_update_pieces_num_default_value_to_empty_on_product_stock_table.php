<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePiecesNumDefaultValueToEmptyOnProductStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_stock', function (Blueprint $table) {
            DB::unprepared("ALTER TABLE `product_stock` CHANGE `pieces_num` `pieces_num` VARCHAR(30) NOT NULL DEFAULT '' COMMENT '单位数量（可选）';");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_stock', function (Blueprint $table) {
            //
        });
    }
}
