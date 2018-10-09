<?php

namespace App\Models;

use App\Models\Model;
use App\Exceptions\BusinessException;

class Pick extends Model
{
    protected $table = 'pick';

    const STATUS_CANCEL    = Order::STATUS_CANCEL;// 订单已取消
    const STATUS_DEFAULT   = Order::STATUS_DEFAULT;// 待拣货（默认状态）
    const STATUS_PICKING   = Order::STATUS_PICKING;// 拣货中
    const STATUS_PICK_DONE = Order::STATUS_PICK_DONE;// 已拣货
    const STATUS_WAITING   = Order::STATUS_WAITING;// 待出库（已验货）
    const STATUS_SENDING   = Order::STATUS_SENDING;// 配送中
    const STATUS_SUCCESS   = Order::STATUS_SUCCESS;// 已收货

    const VERIFY_STATUS_INIT = Order::VERIFY_STATUS_INIT;//未验货
    const VERIFY_STATUS_DONE = Order::VERIFY_STATUS_DONE;//已验货
    const VERIFY_STATUS_ERR  = Order::VERIFY_STATUS_ERR;//验货有误

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];

    protected $appends = [
        'pick_name',
        'status_name',
        'verify_status_name',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function order()
    {
        return $this->belongsTo('App\Models\Order', 'order_id', 'id');
    }

    public function orderItems()
    {
        return $this->hasMany('App\Models\OrderItem', 'shipment_num', 'shipment_num');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id');
    }

    public function feature()
    {
        return $this->belongsTo('App\Models\WarehouseFeature', 'warehouse_feature_id', 'id');
    }

    // public function operatorUser()
    // {
    //     return $this->belongsTo('App\Models\User', 'operator', 'id');
    // }

    public function kep()
    {
        return $this->hasOne('App\Models\Kep', 'shipment_num', 'shipment_num');
    }

    public function checkHistory()
    {
        return $this->hasMany('App\Models\PickCheckHistory', 'shipment_num', 'shipment_num');
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function getPickNameAttribute()
    {
        $name = '（未知特性）拣货单';

        if (isset($this->feature, $this->feature['name_'.app('translator')->locale()])) {
            $name = $this->feature['name_'.app('translator')->locale()].'拣货单';
        }

        return $name;
    }

    /**
     * @return string
     */
    public function getStatusNameAttribute()
    {
        $name = '';

        switch ($this->status) {    
            case Pick::STATUS_CANCEL:
                $name = '订单已取消';
                break;
            case Pick::STATUS_DEFAULT:
                $name = '待拣货';
                break;
            case Pick::STATUS_PICKING:
                $name = '拣货中';
                break;
            case Pick::STATUS_PICK_DONE:
                $name = '已拣货';
                break;
            case Pick::STATUS_WAITING:
                $name = '待出库';
                break;
            case Pick::STATUS_SENDING:
                $name = '配送中';
                break;
            case Pick::STATUS_SUCCESS:
                $name = '已收货';
                break;
            default:
                break;
        }

        return $name;
    }

    /**
     * @return string
     */
    public function getVerifyStatusNameAttribute()
    {
        $name = '';

        switch ($this->verify_status) {
            case Pick::VERIFY_STATUS_INIT:
                $name = '未验货';
                break;
            case Pick::VERIFY_STATUS_DONE:
                $name = '已验货';
                break;
            case Pick::VERIFY_STATUS_ERR:
                $name = '验货有误';
                break;
            default:
                break;
        }

        return $name;
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
    public function scopeHasKeywords($query, $keywords)
    {
        return $query->where('out_sn', 'like', '%' . $keywords . '%')
                     ->orWhere('express_code', 'like', '%' . $keywords . '%')
                     ->orWhere('receiver_fullname', 'like', '%' . $keywords . '%');
    }

    /*
    |--------------------------------------------------------------------------
    | Operations
    |--------------------------------------------------------------------------
    */

    /**
     * 生成拣货单号
     *
     * @param  string $warehouse_code
     * @param  integer $num
     * @return string
     */
    protected static function makeShipmentNum($warehouse_code, $num)
    {
        return 'PP' . strtoupper($warehouse_code) . encodeData(date('Y-m-d')) . encodeseq($num);
    }

    public function checkStatusWhenVerifying()
    {
        if ($this->status == Pick::STATUS_CANCEL) {
            throw new BusinessException('拣货单已取消');
        }

        if ($this->status == Pick::STATUS_DEFAULT) {
            throw new BusinessException('请先打印拣货单');
        }

        if ($this->status == Pick::STATUS_PICKING) {
            throw new BusinessException('请先拣货');
        }
    }
}