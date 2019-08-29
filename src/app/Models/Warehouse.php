<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Warehouse extends Model
{
    protected $table = 'warehouse';

    public $default = 0;
    public $warehouse_feature = "-";

    const TYPE_OPEN = 1;
    const TYPE_PERSONAL = 2;

    const AVAILABLE = 0;
    const UNAVAILABLE = 1;

    public  $timestamps = true;
    protected  $fillable = ['name_cn','code','area','city','province','street','door_no','owner_id'];
    protected $guarded = [];

    public function fromDateTime($value)
    {
        return strtotime($value);
    }

    //默认仓库标识
    public  function setDefault(int $default)
    {
        $this->default =$default;
        $this->warehouse_feature = "默认仓库";
        return $this;
    }


    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }


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

    public function getWarehouseAddressAttribute()
    {
        return $this->country.$this->city.$this->street.$this->door_no;
    }


    public function getIsDefaultWarehouseAttribute()
    {
        return $this->default;
    }

    public function getWarehouseFeatureAttribute()
    {
        return $this->warehouse_feature;
    }

    /**
     * 仓库是否开启双语
     **/
    public static function isEnabledLang(int $id)
    {
        try
        {
            return Warehouse::find($id)->is_enabeld_lang;
        }
        catch(\Exception $ex)
        {
            
        }

        return false;
        
    }

    /**
     * 只要自己创建人不重复就行了
     */
    public static function no($owner_id = 0)
    {
        $key = "CMS-WAREHOUSE-".$owner_id;
        $value = Cache::increment($key);
        $code =  sprintf("W%02s", enid($value));
        return $code;
    }
}
