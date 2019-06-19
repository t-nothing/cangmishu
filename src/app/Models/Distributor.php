<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Distributor extends Model
{
    use SoftDeletes;
    protected $table ="distributor";
    protected $fillable =['user_id','name_cn','name_en'];
    protected $guarded = [];
    protected  $appends=['name'];

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

    public function  ScopeWhose($query, $owner_id){
        return $query->where('user_id',$owner_id);
    }



    public function getNameAttribute()
    {
        $lang = app('translator')->getLocale();
        if(in_array($lang,['en','cn','zh-CN'])){
            if(in_array($lang,['cn','zh-CN'])){
                $lang = "cn";
            }
        }else{
            $lang = "cn";
        }
        $name = "name_".$lang;
        return $this->$name;
    }
}



