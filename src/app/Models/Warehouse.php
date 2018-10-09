<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

class Warehouse extends Model
{
    protected $table = 'warehouse';

    const TYPE_OPEN = 1;
    const TYPE_PERSONAL = 2;

    const AVAILABLE = 0;
    const UNAVAILABLE = 1;

    public function WarehouseArea()
    {
        return $this->hasMany('App\Models\WarehouseArea', 'warehouse_id', 'id');
    }

    public function Application()
    {
        return $this->hasMany('App\Models\LeaseApplicationInfo', 'warehouse_id', 'id');
    }

    public function batch()
    {
        return $this->hasMany('App\Models\Batch', 'warehouse_id', 'id');
    }

    public function order()
    {
        return $this->hasMany('App\Models\Order', 'warehouse_id', 'id');
    }

    public function productStock()
    {
        return $this->hasMany('App\Models\ProductStock', 'warehouse_id', 'id');
    }

    public function employees()
    {
        return $this->hasMany('App\Models\WarehouseEmployee', 'warehouse_id', 'id');
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
        return $query->where('name_cn', 'like', '%' . $keywords . '%')
            ->orWhere('name_en', 'like', '%' . $keywords . '%')
            ->orWhere('code', 'like', '%' . $keywords . '%');
    }

    /**
     * 限制查询属于指定用户（仓库使用者）。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfUser($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }


    /**
     * 已使用？
     *
     * @return bool
     */
    public function isUsed()
    {
        return $this->is_used == Warehouse::UNAVAILABLE;
    }


    /**
     * 给当前用户添加仓库权限
     * @param $warehouse_id
     * @param $type
     * @return bool
     */
    static function addEmployee($role_id,$user_id,$warehouse_id)
    {
        $employee = new WarehouseEmployee();

        $data = [
            'warehouse_id' => $warehouse_id,
            'user_id' => $user_id,
            'role_id' => $role_id,
            'operator' => Auth::id(),
        ];
        WarehouseEmployee::binds($employee,$data);
        return $employee->save();
    }

}
