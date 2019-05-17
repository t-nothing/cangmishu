<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Distributor extends Model
{
    use SoftDeletes;
    protected $table ="distributor";
    protected $fillable =['user_id','name_cn','name_en'];
    protected $guarded = [];


    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

    public function  ScopeWhose($query, $owner_id){
        return $query->where('user_id',$owner_id);
    }
}
