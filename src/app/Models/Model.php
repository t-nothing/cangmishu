<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    protected static $_instance = [];

    /**
     * The number of models to return for pagination.
     *
     * @var int
     */
    protected $perPage = 10;

    /**
     * The storage format of the model's date columns.
     * 'U' means the unix timestamp
     *
     * @var string
     */
    protected $dateFormat = 'U';

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
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * 限制查询属于指定用户。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhose($query, $user_id)
    {
        return $query->where('owner_id', $user_id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfWarehouse($query, $warehouse_id)
    {
        return $query->where('warehouse_id', $warehouse_id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Customs
    |--------------------------------------------------------------------------
    */

    /**
     * 获得单实例
     * @return static
     */
    public static function getIns(){
        $class = get_called_class();
        if (!array_key_exists($class,self::$_instance)) {
            self::$_instance[$class] = new static();
            return self::$_instance[$class];
        }
        return self::$_instance[$class];
    }

    /**
     * 模型白名单字段赋值
     * @param $model
     * @param $data
     */
    public static function binds($model, $data)
    {
        foreach ($data as $k=>$v) {
            if (in_array($k, $model->fillable)) {
                $model->attributes[$k] = $v;
            }
        }
    }
}
