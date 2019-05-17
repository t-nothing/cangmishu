<?php

namespace App\Models;


class UserGroupRel extends  Model
{

    protected  $table = 'user_group_rel';
    protected  $fillable = ['user_id', 'group_id'];
    public  $timestamps = true;

    public function fromDateTime($value) {
        return $value;
    }

}