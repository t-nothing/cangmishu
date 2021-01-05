<?php

namespace App\Models;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\ProductStockLocation;


class WarehouseLocation extends Model
{
    protected $table = 'warehouse_location';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected  $fillable =['warehouse_id','warehouse_area_id','code','is_enabled','passage','row','col','floor','remark','owner_id','capacity'];
    protected $guarded =[];

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

    public function stock()
    {
        return $this->hasMany('App\Models\ProductStock','warehouse_location_id','id');
    }
    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

    public function ScopeOfWarehouse($query, $warehouse_id)
    {
        return $query->where('warehouse_id', $warehouse_id);
    }

    public function  ScopeEnabled($query)
    {
        return $query->where('is_enabled',1);
    }

    /**
     * 更新排序值
     * 主要用于拣货这块
     */
    public static function updateSortNum($areaId, $sortNum)
    {

        $model = self::where('warehouse_area_id', $areaId);
        $model->update(['sort_num' =>  $sortNum]);

        $dataList = $model->select("id")->get();
        foreach ($dataList as $key => $row) {
            ProductStockLocation::updateSortNum($row["id"], $sortNum);
        }
        
        app('log')->info('更新排序值', [
            'warehouse_area_id'=>$areaId,
            'sort_num'=>$sortNum,
        ]);
    }

    /**
     * 返回的code
     */
    public static function getCode( $id)
    {
        try
        {
            $id = intval($id);
            return Self::findOrFail($id)->code;
        }
        catch(ModelNotFoundException $x)
        {

        }

        return "0";
        
    }
}
