<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNameAndRemarkFromNullToEmptyStringOnOrderItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_stock', function (Blueprint $table) {
            DB::unprepared("
                UPDATE `order_item` SET name = '' WHERE name IS NULL;
                UPDATE `order_item` SET remark = '' WHERE remark IS NULL;
                ALTER TABLE `order_item` CHANGE `name` `name` VARCHAR(255) CHARACTER 
                    SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品名字', CHANGE `remark` `remark` VARCHAR(255) CHARACTER 
                    SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注';
            ");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
