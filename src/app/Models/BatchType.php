<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class BatchType extends Model
{
    use SoftDeletes;

    protected $table = 'batch_type';

    const STATUS_PREPARE    = 1;// 待入库
    const STATUS_PROCEED    = 2;// 入库中
    const STATUS_ACCOMPLISH = 3;// 入库完成
    const STATUS_CANCEL     = 4;// 取消

    protected $hidden = [
        'parent_id',
        'deleted_at',
    ];

    protected  $fillable = ['name','is_enabled'];

    protected  $guarded = [];
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

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

    public  function ScopeOfWhose($query,$owner_id)
    {
        return $query->where('owner_id',$owner_id);
    }

    public function scopeOfWarehouse($query, $warehouse_id)
    {
        return $query->where('warehouse_id', $warehouse_id);
    }

}
