<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class ShopPaymentMethod extends Model
{
    use SoftDeletes;
    protected $table = 'shop_payment_mentod';


    public function Shop()
    {
        return $this->belongsTo('App\Models\Shop', 'shop_id', 'id');
    }

}
