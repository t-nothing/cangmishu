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
}