<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class ShopSenderAddress extends Model
{
    use SoftDeletes;
    protected $table = 'shop_sender_address';
    protected $fillable  = ['shop_id', 'is_default', 'country', 'province', 'city', 'district', 'address', 'postcode', 'fullname', 'phone']; 


    public function Shop()
    {
        return $this->belongsTo('App\Models\Shop', 'shop_id', 'id');
    }

}
