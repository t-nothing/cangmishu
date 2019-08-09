<?php
/**
 * Created by PhpStorm.
 * User: NLE-Tech
 * Date: 2019/5/7
 * Time: 15:40
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Models;
class Model extends  Models
{
    protected static $_instance = [];

    public function fromDateTime($value)
    {
        return strtotime($value);
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

    public function castsTo($key)
    {
        // return $this->serializeDate($this->asDateTime($this->{$key}));

        $attributes = $this->attributes;

        if (! is_null($value = array_get($this->getCasts(), $key))) {
            if (! array_key_exists($key, $attributes)) {
                return;
            }

            // Here we will cast the attribute. Then, if the cast is a date or datetime cast
            // then we will serialize the date for the array. This will convert the dates
            // to strings based on the date format specified for these Eloquent models.
            $attributes[$key] = $this->castAttribute(
                $key, $attributes[$key]
            );

            // If the attribute cast was a date or a datetime, we will serialize the date as
            // a string. This allows the developers to customize how dates are serialized
            // into an array without affecting how they are persisted into the storage.
            if ($attributes[$key] &&
                ($value === 'date' || $value === 'datetime')) {
                $attributes[$key] = $this->serializeDate($attributes[$key]);
            }

            if ($attributes[$key] && $this->isCustomDateTimeCast($value)) {
                $attributes[$key] = $attributes[$key]->format(explode(':', $value, 2)[1]);
            }
        }

        return $attributes[$key];
    }


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

    public  function ScopeOfWarehouse($query, $warehouse_id)
    {
        return $query->where('warehouse_id', $warehouse_id);
    }
}