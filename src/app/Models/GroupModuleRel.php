<?php
/**
 * Created by PhpStorm.
 * User: NLE-Tech
 * Date: 2019/2/19
 * Time: 18:27
 */

namespace App\Models;


class GroupModuleRel extends  Model
{
    protected  $table = 'group_module_rel';
    protected  $fillable = ['group_id', 'module_id'];
    public  $timestamps = true;

    public function fromDateTime($value) {

        return parent::fromDateTime($value);
    }


}