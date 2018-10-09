<?php

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('role')->insert([
            [
	            'id' => 1,
                'name_cn' => '超级管理员',
                'name_en' => 'root',
	            'description' => 'root',
	            'type' => 1,
                'is_system' => 1,
	        ],
            [
                'id' => 2,
                'name_cn' => '管理员',
                'name_en' => 'admin',
	            'description' => '管理员',
	            'type' => 1,
                'is_system' => 1,
            ],
            [
                'id' => 3,
                'name_cn' => '仓库管理员',
                'name_en' => 'manager',
                'description' => '仓库管理员',
                'type' => 3,
                'is_system' => 1,
            ]
        ]);
    }
}
