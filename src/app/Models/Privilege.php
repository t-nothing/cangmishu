<?php

namespace App\Models;

use App\Models\Model;

class Privilege extends Model
{
    const TYPE_SYS   = 1;// 系统
    const TYPE_OWNER = 2;// 产权方
    const TYPE_USER  = 3;// 使用者

    protected $table = 'privilege';

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function privileges()
    {
        return $this->belongsTo('App\Models\Shelf', 'shelf_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

	/**
     * 限制查询只包括指定关键字。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasKeyword($query, $keywords)
    {
        return $query->where('name', 'like', '%' . $keywords . '%');
    }
}
