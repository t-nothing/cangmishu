<?php

use Illuminate\Database\Seeder;

class RoleModuleRelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $owner = range(1,21);
        $renter = range(1,6);
        $owner_rel = collect($owner)->map(function($v){
            return [
                'role_id'=>2,
                'module_id'=>$v,
                'created_at'=>time(),
                'updated_at'=>time()
            ];
        })->toArray();
        $renter_rel = collect($renter)->map(function($v){
            return [
                'role_id'=>3,
                'module_id'=>$v,
                'created_at'=>time(),
                'updated_at'=>time()
            ];
        })->toArray();
        $inserts = array_merge($owner_rel,$renter_rel);
        DB::table('role_module_rel')->insert($inserts);
    }
}
