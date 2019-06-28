<?php

use Illuminate\Database\Seeder;

class RemoveModuleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $module = \App\Models\Modules::where('name',"首页")->first();
        if($module){
            $module->delete();
            \App\Models\GroupModuleRel::where('module_id',$module->id)->delete();
        }
    }
}
