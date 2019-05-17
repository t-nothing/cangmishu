<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class Init extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $path =  __DIR__ . DIRECTORY_SEPARATOR . 'schema.sql';

        if (file_exists($path)) {
            DB::unprepared(file_get_contents($path));
        }
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
