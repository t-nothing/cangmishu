<?php

namespace App\Models;

class UserApp extends Model
{
    protected $table = 'user_app';

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'bind_user_id', 'id');
    }

    public function warehouse()
    {
    	return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id');
    }
}
