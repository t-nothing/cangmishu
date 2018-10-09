<?php

namespace App\Models;

use App\Exceptions\BusinessException;

class WarehouseArea extends Model
{
	const AREA_FUNCTION_RECEIVING = 1;// 收货
    const AREA_FUNCTION_PICKING   = 2;// 拣货
    const AREA_FUNCTION_STOCKING  = 3;// 备货
    const AREA_FUNCTION_SHIPPING  = 4;// 集货

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

    protected $appends = [
        'function_names',
    ];

    protected $function_translations = [
    	WarehouseArea::AREA_FUNCTION_RECEIVING => [
    		'cn' => '收货区',
    		'en' => 'Receiving Area',
    	],
    	WarehouseArea::AREA_FUNCTION_PICKING => [
    		'cn' => '拣货区',
    		'en' => 'Picking Area',
    	],
    	WarehouseArea::AREA_FUNCTION_STOCKING => [
    		'cn' => '备货区',
    		'en' => 'Stocking Area',
    	],
    	WarehouseArea::AREA_FUNCTION_SHIPPING => [
    		'cn' => '集货区',
    		'en' => 'Shipping Area',
    	],
    ];

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

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * Get the area's functions name.
     *
     * @return string
     */
    public function getFunctionNamesAttribute()
    {
        $r = [];

        if ($this->functions && $this->function_translations) {
            foreach ($this->functions as $k => $v) {
                $r[] = $this->translateFunctionTo($v);
            };
        }

        return implode(',', $r);
    }

    /*
    |--------------------------------------------------------------------------
    | Operations
    |--------------------------------------------------------------------------
    */

    /**
     * 翻译
     *
     * @return string
     */
    public function translateFunctionTo($function, $lang = 'cn')
    {
        return isset($this->function_translations[$function][$lang])
            ? $this->function_translations[$function][$lang]
            : '';
    }

    public function delete()
    {
        if ($this->locations->count() > 0) {
            throw new BusinessException('货区下有货位，无法删除');
        }

        return parent::delete();
    }
}
