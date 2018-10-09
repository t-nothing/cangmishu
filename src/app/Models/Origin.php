<?php

namespace App\Models;

use App\Models\Model;

class Origin extends Model
{
    protected $table = 'product_origin';

    /**
     * 限制查询属于指定用户。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhose($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }
}