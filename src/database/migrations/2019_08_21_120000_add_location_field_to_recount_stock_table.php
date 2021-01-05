<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AddLocationFieldToRecountStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('recount_stock', function (Blueprint $table)  { $table->string('location_code')->comment('货位CODE');
            $table->integer('location_id')->comment('货位ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('recount_stock', function (Blueprint $table) {
            $table->dropColumn('location_code');
            $table->dropColumn('location_id');

        });
    }
}
