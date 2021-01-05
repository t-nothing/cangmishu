<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//
class AddIndexToSkuMarkLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('sku_mark_log', function (Blueprint $table)  {                 
            $table->index(['warehouse_code', 'spec_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sku_mark_log', function (Blueprint $table)  {                 
            $table->dropIndex(['warehouse_code', 'spec_id']);
        });
    }
}
