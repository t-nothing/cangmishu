<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserTableAddInformationColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('contact_address')
                ->default('')
                ->comment('联系地址');

            $table->string('contact')
                ->default('')
                ->comment('联系人');
            $table->string('industry')
                ->default('')
                ->comment('行业');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['contact_address', 'contact', 'industry']);
        });
    }
}
