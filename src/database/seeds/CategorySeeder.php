<?php

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('category')->insert([
            [
	            'name_cn' => '蔬菜',
	            'name_en' => 'Vegetable',
	            'parent_id' => 0,
	            'warning_stock' => 10,
                // 保质期
	        ],
            [
                'name_cn' => '水果',
                'name_en' => 'Fruits',
                'parent_id' => 0,
                'warning_stock' => 10,
                // 保质期
            ],
            [
                'name_cn' => '海鲜',
                'name_en' => 'Seafood',
                'parent_id' => 0,
                'warning_stock' => 10,
                // 保质期
            ],
            [
                'name_cn' => '米面',
                'name_en' => 'Rice & Noodle',
                'parent_id' => 0,
                'warning_stock' => 10,
                // 保质期
            ],
            [
                'name_cn' => '调料',
                'name_en' => 'Seasoning',
                'parent_id' => 0,
                'warning_stock' => 10,
                // 保质期
            ],
            [
                'name_cn' => '零食',
                'name_en' => 'Snack',
                'parent_id' => 0,
                'warning_stock' => 10,
                // 保质期
            ],
            [
                'name_cn' => '牛奶',
                'name_en' => 'Milk',
                'parent_id' => 0,
                'warning_stock' => 10,
                // 保质期，批次号
            ],
            [
                'name_cn' => '软饮',
                'name_en' => 'Soft Drink',
                'parent_id' => 0,
                'warning_stock' => 10,
                // 保质期
            ],
            [
                'name_cn' => '酒精类',
                'name_en' => 'Liquor',
                'parent_id' => 0,
                'warning_stock' => 10,
                // 保质期
            ],
            [
                'name_cn' => '烟草',
                'name_en' => 'Tobacco',
                'parent_id' => 0,
                'warning_stock' => 10,
                // 保质期
            ],
            [
                'name_cn' => '餐具',
                'name_en' => 'Tableware',
                'parent_id' => 0,
                'warning_stock' => 10,
            ],
            [
                'name_cn' => '服装',
                'name_en' => 'Clothing',
                'parent_id' => 0,
                'warning_stock' => 10,
            ],
        ]);
    }
}
