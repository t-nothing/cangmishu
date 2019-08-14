<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductStockIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_stock', function (Blueprint $table) {
            // $table->dropIndex(['relevance_code']); // 删除 'relevance_code' 索引
            $table->index(['relevance_code']); // 删除 'relevance_code' 索引
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
