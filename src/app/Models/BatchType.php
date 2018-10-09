<?php

namespace App\Models;

use App\Models\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class BatchType extends Model
{
    use SoftDeletes;

    protected $table = 'batch_type';

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function warehouseArea()
    {
        return $this->belongsTo('App\Models\WarehouseArea', 'area_id', 'id');
    }

    public function batches()
    {
    	return $this->hasMany('App\Models\Batch', 'type_id', 'id');
    }
}
