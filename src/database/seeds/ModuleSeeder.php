<?php

use Illuminate\Database\Seeder;

class ModuleSeeder extends  Seeder
{
    public function run()
    {
        DB::table('modules')->insert([
            [
                'name'=>"首页",
                'parent_id'=>0,
                'created_at'=>time(),
                'updated_at'=>time()
            ],
            [
                'name'=>"入库",
                'parent_id'=>0,
                'created_at'=>time(),
                'updated_at'=>time()
            ],
            [
                'name'=>"出库",
                'parent_id'=>0,
                'created_at'=>time(),
                'updated_at'=>time()
            ],
            [
                'name'=>"库存",
                'parent_id'=>0,
                'created_at'=>time(),
                'updated_at'=>time()
            ],
            [
                'name'=>"设置",
                'parent_id'=>0,
                'created_at'=>time(),
                'updated_at'=>time()
            ],
        ]);
    }
}