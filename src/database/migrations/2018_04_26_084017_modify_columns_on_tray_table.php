<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsOnTrayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tray', function (Blueprint $table) {
            $table->integer('created_at')->comment('创建时间')->nullable()->change();
            $table->integer('updated_at')->comment('更改时间')->nullable()->change();
            $table->string('plies', 11)->comment('所在层数')->nullable()->change();
        });

        Schema::table('shelf', function (Blueprint $table) {
            $table->integer('created_at')->comment('创建时间')->nullable()->change();
            $table->integer('updated_at')->comment('更改时间')->nullable()->change();
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
