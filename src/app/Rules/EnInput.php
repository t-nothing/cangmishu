<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Warehouse;

/**
 * 判断是否需要输入英文
 */
class EnInput implements Rule
{
    var $warehouse_id;
    var $attribute;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($warehouse_id)
    {
        $this->warehouse_id = $warehouse_id;
    }

    /**
     * 判断验证规则是否通过。
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        $isEnabled =  Warehouse::isEnabledLang($this->warehouse_id);
        if($isEnabled)
        {
            if(trim($value) == "")
            {
                return false;
            }
        }

        return true;
    }

    /**
     * 获取验证错误消息。
     *
     * @return string
     */
    public function message()
    {
        return "{$this->attribute} 是必填项";
    }
}
