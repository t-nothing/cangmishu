<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductSpecTableSetCodeUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_spec', function (Blueprint $table) {
            $table->dropUnique('product_spec_owner_id_relevance_code_unique');
            $table->unique(['relevance_code', 'warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_spec', function (Blueprint $table) {
            $table->dropUnique(['relevance_code', 'warehouse_id']);
            $table->unique(['relevance_code', 'owner_id']);
        });
    }
}
