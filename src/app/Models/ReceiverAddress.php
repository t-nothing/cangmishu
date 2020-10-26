<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceiverAddress extends Model
{
    use SoftDeletes;
    protected $table ="receiver_address";
    protected  $fillable =['fullname','phone','country','province', 'city','district','address','owner_id','street', 'door_no'];
    protected $guarded = [];

    public function fromDateTime($value)
    {
        return parent::fromDateTime($value);
    }

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

    public function getFullAddressAttribute()
    {
        return $this->country.$this->province.$this->city.$this->district.$this->address;
    }
}
