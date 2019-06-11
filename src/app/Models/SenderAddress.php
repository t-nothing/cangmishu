<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SenderAddress extends  Model
{
    use SoftDeletes;
    protected $table ="sender_address";
    protected  $fillable =['fullname','phone','country','province', 'city','district','address','owner_id'];
    protected $guarded = [];

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }


    public function getFullAddressAttribute()
    {
        return $this->country.$this->province.$this->city.$this->district.$this->address;
    }

}