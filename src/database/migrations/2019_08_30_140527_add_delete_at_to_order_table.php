<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeleteAtToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order', function (Blueprint $table)  {      
            $table->integer('deleted_at')->comment('删除')->nullable();           
        });
        Schema::table('order_item', function (Blueprint $table)  {      
            $table->integer('deleted_at')->comment('删除')->nullable();           
        });
        Schema::table('warehouse', function (Blueprint $table)  {      
            $table->integer('deleted_at')->comment('删除')->nullable();           
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_item', function (Blueprint $table)  { 
            $table->dropColumn('deleted_at');                 
        });

        Schema::table('order', function (Blueprint $table)  { 
            $table->dropColumn('deleted_at');                 
        });

        Schema::table('warehouse', function (Blueprint $table)  { 
            $table->dropColumn('deleted_at');                 
        });
    }
}
