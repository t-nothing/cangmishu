<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Models;


class BatchType extends Model
{

    protected $table = 'batch_type';

    const STATUS_PREPARE    = 1;// 待入库
    const STATUS_PROCEED    = 2;// 入库中
    const STATUS_ACCOMPLISH = 3;// 入库完成
    const STATUS_CANCEL     = 4;// 取消

    protected $hidden = [
        'parent_id',
        'deleted_at',
    ];

    protected  $fillable = ['name','is_enabled','owner_id','warehouse_id'];

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
