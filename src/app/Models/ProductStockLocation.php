<?php

namespace App\Models;

use App\Models\ProductStock;
use App\Models\WarehouseLocation;
use App\Models\WarehouseArea;
use Illuminate\Support\Facades\DB;

class ProductStockLocation extends Model
{
    
    protected $table = 'product_stock_location';

    public     $timestamps = true;

    protected $guarded  =[];

    protected $moveQty = 0;


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'warehouse_location_code',
    ];


    public function setMoveQty(int $v)
    {
        $this->moveQty = $v;
        return $this;
    }


    public function getMoveQty()
    {
        return $this->moveQty;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function stock()
    {
        return $this->belongsTo('App\Models\ProductStock', 'stock_id', 'id');
    }

    public function spec()
    {
        return $this->belongsTo('App\Models\ProductSpec', 'spec_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo('App\Models\WarehouseLocation', 'warehouse_location_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany('App\Models\ProductStockLog', 'product_stock_location_id', 'id');
    }


    /*
    |--------------------------------------------------------------------------
    | 属性
    |--------------------------------------------------------------------------
    */
    public  function  getSpecProductAttribute()
    {

        $arr = [
            "id"                =>$this->spec->id,
            "produc_id"         =>$this->spec->product->id,
            "name_cn"           => sprintf("%s-%s", $this->spec->product->name_cn, $this->spec->name_cn),
            "name_en"           => sprintf("%s-%s", $this->spec->product->name_en, $this->spec->name_en),
            "photos"            => $this->spec->product->photos,
        ];
        $arr["name"] = $arr["name_en"];
        if(app('translator')->locale() == "cn")
        {
            $arr["name"] = $arr["name_cn"];
        }
        return $arr;
    }

    public  function  getProductNameAttribute()
    {
        $name = $this->spec?($this->spec->product_name?:""):"";
        return $name;
    }

    /*
    |--------------------------------------------------------------------------
    | 位置代码
    |--------------------------------------------------------------------------
    */
    public  function  getWarehouseLocationCodeAttribute()
    {

        return WarehouseLocation::getCode($this->warehouse_location_id);
    }


    /*
    |--------------------------------------------------------------------------
    | 位置代码
    |--------------------------------------------------------------------------
    */
    public  function  getWarehouseAreaFunctionAttribute()
    {
        $area = new WarehouseArea();
        return $area->translateFunctionBySortNum($this->sort_num);
    }


    /**
     * 移动货位
     * @param allowExceptionNum 是否允许超量
     */
    public function moveTo(int $qty, WarehouseLocation $location)
    {
  

        if( $qty > $this->shelf_num)
        {
            throw new \Exception("超出货架最大库存", 1);
        }

        if($this->warehouse_location_id == $location->id)
        {
            throw new \Exception("货位不能相同", 1);
        }

        $newLocation = Self::updateOrCreate(
            [
                'stock_id'                  => $this->stock_id,
                'spec_id'                   => $this->spec_id,
                'sku'                       => $this->sku,
                'ean'                       => $this->ean,
                'relevance_code'            => $this->relevance_code,
                'owner_id'                  => $this->owner_id,
                'warehouse_id'              => $this->warehouse_id,
                //发生变化的地方
                'warehouse_location_id'     => $location->id,
                'sort_num'                  => $location->sort_num??0,
            ]
        );

        $newLocation->increment('shelf_num', $qty);
        //最小就是0
        $this->shelf_num  = max($this->shelf_num - $qty , 0);


        // 如果当前货位上面没有东西了
        // if($this->shelf_num <= 0 )
        // {
        //     $this->delete();
        // }

       $this->save();

        return $newLocation;
    }

    /**
     * 调整货位
     * @param allowExceptionNum 是否允许超量
     */
    public function adjustShelfNum(int $qty)
    {
        $this->shelf_num  = max($qty, 0);
        $this->save();
        return $this;
    }

    /**
     * 更新排序值
     * 主要用于拣货这块
     */
    public static function updateSortNum($locationId, $sortNum = 0)
    {
        self::where('warehouse_location_id', $locationId)->update(['sort_num' =>  $sortNum]);


        app('log')->info('更新排序值', [
            'warehouse_location_id'=>$locationId,
            'sort_num'=>$sortNum,
        ]);
    }
}