<?php

namespace App\Models;

use App\Models\Model;

class PickCheckHistory extends Model
{
    protected $table = 'pick_check_history';

    protected $dates = [
        'created_at',
        'updated_at',
        'date',
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
        'date' => 'date:Y-m-d',
    ];

    protected $appends = [];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    //

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    //
}
