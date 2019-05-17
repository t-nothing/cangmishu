<?php

namespace App\Models;

class Groups extends  Model
{
    protected  $table = 'groups';
    protected  $fillable = ['name', 'user_id','warehouse_id', 'role_id','remark','owner_id'];
    public  $timestamps = true;
    protected $hidden=['pivot'];
    public  $appends = ['user_amount'];

    public function fromDateTime($value) {

        return parent::fromDateTime($value);
    }

    public function modules()
    {
        return $this->belongsToMany('App\Models\Modules', 'group_module_rel', 'group_id', 'module_id');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_group_rel', 'group_id', 'user_id');
    }

    public function  owner()
    {
        return $this->hasOne('App\Models\User',"id",'owner_id');
    }

    public  function  warehouse()
    {
        return $this->hasOne('App\Models\Warehouse',"id",'warehouse_id');
    }

    //Attribute

    public function getUserAmountAttribute(){
        return $this->users()->count();
    }

}