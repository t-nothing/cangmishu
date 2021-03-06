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

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model as Models;
use Illuminate\Support\Arr;

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

        if (! is_null($value = Arr::get($this->getCasts(), $key))) {
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

    public  function ScopeOfShop($query, $shop_id)
    {
        return $query->where('shop_id', $shop_id);
    }

    public  function ScopeOfShopUser($query, $shop_id, $shop_user_id)
    {
        return $query->where('shop_id', $shop_id)->where('shop_user_id', $shop_user_id);
    }

    /**
     * 为数组 / JSON 序列化准备日期。
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
