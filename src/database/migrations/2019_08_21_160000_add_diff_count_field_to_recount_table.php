<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiffCountFieldToRecountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('recount', function (Blueprint $table)  { 
            $table->integer('diff_count')->comment('差别数字')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('recount', function (Blueprint $table) {
            $table->dropColumn('diff_count');

        });
    }
}
