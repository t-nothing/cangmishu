<?php

namespace App\Models;

class Modules extends  Model
{
    protected  $table = 'modules';
    protected  $fillable = ['name', 'parent_id'];
    public     $timestamps = true;
    protected $hidden=['pivot'];

    public function fromDateTime($value) {

        return parent::fromDateTime($value);
    }


    public function groups()
    {
        return $this->belongsToMany('App\Models\Groups', 'group_module_rel', 'module_id', 'group_id');
    }

}