<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToUserCategoryWarning extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_category_warning', function (Blueprint $table) {
            $table->index(['user_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_category_warning', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'category_id']); 
        });
    }
}
