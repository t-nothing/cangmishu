<?php

namespace App\Models;

use App\Models\Model;

class UserEmployee extends Model
{
    protected $table = 'user_employee';

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function boss()
    {
        return $this->belongsTo('App\Models\User', 'boss_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
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
        $user_ids = User::where('email', 'like', '%' . $keywords . '%')->pluck('id');

        return $query->whereIn('user_id', $user_ids);
    }
}
