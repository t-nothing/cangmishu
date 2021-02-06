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

class WarehouseArea extends Model
{
    protected $table = 'warehouse_area';

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
        'functions' => 'array',
    ];

    protected  $fillable = ['warehouse_id','name_cn','code','is_enabled','remark','owner_id'];
    protected  $guarded = [];

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id');
    }

    public function locations()
    {
        return $this->hasMany('App\Models\WarehouseLocation', 'warehouse_area_id', 'id');
    }

    public function feature()
    {
        return $this->belongsTo('App\Models\WarehouseFeature', 'warehouse_feature_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
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
     * @author liusen
     */
    public function scopeHasKeyword($query, $keywords)
    {
        return $query->where('code', 'like', '%' . $keywords . '%')
                     ->orWhere('name_cn', 'like', '%' . $keywords . '%')
                     ->orWhere('name_en', 'like', '%' . $keywords . '%');
    }

    public function scopeOfWarehouse($query, $warehouse_id)
    {
        return $query->where('warehouse_id', $warehouse_id);
    }

    public function scopeWhose($query, $owner_id)
    {
        return $query->where('owner_id', $owner_id);
    }

}
