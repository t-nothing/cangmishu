<?php

namespace App\Models;

class RecountStock extends Model
{

    protected $table = 'recount_stock';

    protected  $fillable = [];

    protected $guarded  =[];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
        'deleted_at' => 'date:Y-m-d H:i:s',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function recount()
    {
        return $this->belongsTo('App\Models\Recount', 'recount_id', 'id');
    }


}