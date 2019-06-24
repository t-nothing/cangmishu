<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropIndexRelatedToWarehouse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category', function (Blueprint $table) {
            $table->dropIndex("category_warehouse_id_name_cn_unique");
            $table->dropIndex("category_warehouse_id_name_en_unique");
            $table->unique(['owner_id', 'name_cn']);
            $table->unique(['owner_id', 'name_en']);
        });
        Schema::table('order_type', function (Blueprint $table) {
            $table->dropIndex("order_type_warehouse_id_name_unique");
            $table->unique(['owner_id', 'name']);
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
