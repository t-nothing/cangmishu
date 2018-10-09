<?php

namespace App\Models;

use App\Models\Model;

class HomePageNotice extends Model
{
    const DISPLAY = 1;
    const HIDE = 0;

    protected $table = 'home_page_notice';

    protected $dates = [
        'created_at',
        'updated_at',
        'notice_time',
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
        'notice_time' => 'date:Y-m-d H:i:s',
    ];

    protected $fillable = [
        'notice_type',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

}