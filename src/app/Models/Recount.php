<?php

namespace App\Models;
use Illuminate\Support\Facades\Cache;

class Recount extends Model
{

    protected $table = 'recount';

    protected  $fillable = [];

    protected $guarded  =[];
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
        'plan_time'  => 'date:Y-m-d H:i:s',
        'over_time'  => 'date:Y-m-d H:i:s',
    ];

    public function operatorUser()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id')->withDefault([
            'nickname' => '',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function stocks()
    {
        return $this->hasMany('App\Models\RecountStock', 'recount_id', 'id');
    }


    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id');
    }


    /**
     * @return string
     */
    public function getRecountNoBarcodeAttribute()
    {
        return 'data:image/png;base64,' . app("DNS1D")->getBarcodePNG($this->recount_no, "C128");
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    /**
     * 限制查询属于指定仓库。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfWarehouse($query, $warehouse_id)
    {
        return $query->where('warehouse_id', $warehouse_id);
    }

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
     * 限制查询只包括指定关键字。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @author liusen
     */
    public function scopeHasKeyword($query, $keywords)
    {
        // return $query->where('batch_code', 'like', '%' . $keywords . '%')
        //              ->orWhere('confirmation_number', 'like', '%' . $keywords . '%');
    }

    public static function no()
    {
        $key = "PD".date("Ymd");
        return sprintf("%s%04s", $key, Cache::increment($key));
    }
}