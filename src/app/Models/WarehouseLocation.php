<?php

namespace App\Models;

class WarehouseLocation extends Model
{
    protected $table = 'warehouse_location';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id');
    }

    public function warehouseArea()
    {
        return $this->belongsTo('App\Models\WarehouseArea', 'warehouse_area_id', 'id');
    }
}
