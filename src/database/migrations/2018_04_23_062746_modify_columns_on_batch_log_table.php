<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsOnBatchLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batch_log', function (Blueprint $table) {
            $table->dropColumn('type_id');
            $table->dropColumn('order_sn');
            $table->dropColumn('balance_num');
            $table->dropColumn('ean');
            $table->dropColumn('relevance_code');
            $table->integer('created_at')->nullable()->change();
            $table->integer('updated_at')->nullable()->change();
            $table->integer('warehouse_id')->default(0)->comment('仓库')->change();
            $table->integer('operator')->default(0)->change();
            $table->integer('owner_id')->default(0)->comment('商家')->change();
            $table->integer('batch_id')->after('id')->default(0);
            $table->integer('stock_id')->after('batch_id')->default(0);
            $table->integer('num')->default(0)->comment('操作数量')->change();
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
