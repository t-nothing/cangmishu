<?php

namespace App\Models;

class ShopUserAddress extends Model
{
    protected $table = 'shop_user_addresses';

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Convert a DateTime to a storable string.
     *
     * @param  mixed  $value
     * @return string|null
     */
    public function fromDateTime($value)
    {
        return empty($value) ? $value : $this->asDateTime($value)->format(
            $this->getDateFormat()
        );
    }
}
