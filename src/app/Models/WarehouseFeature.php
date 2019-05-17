<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseFeature extends Model
{
    use SoftDeletes;
    protected $table = 'warehouse_feature';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
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

    protected $guarded =[];

}
