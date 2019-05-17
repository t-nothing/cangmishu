<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexWarehouseIdRelevanceCodeToOrderItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_item', function (Blueprint $table) {
            $table->index(['warehouse_id', 'relevance_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_item', function (Blueprint $table) {
            $table->dropIndex(['warehouse_id', 'relevance_code']);
        });
    }
}
