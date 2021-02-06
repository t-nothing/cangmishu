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
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    protected $table = 'order';

    const STATUS_CANCEL  = 0;// 订单已取消
    const STATUS_DEFAULT = 1;// 待拣货（默认状态）
    // const STATUS_PICK_DONE = 2;// 拣货完成（默认状态）
    const STATUS_PICKING = 2;// 拣货中
    const STATUS_PICK_DONE = 3;// 已拣货
    const STATUS_WAITING = 4;// 待出库（已验货）
    const STATUS_SENDING = 5;// 配送中
    const STATUS_PAID = 6;// 已支付
    const STATUS_SUCCESS = 7;// 已收货

    const ORDER_DELIVERY_TYPE_AIR      = 1;// 空运
    const ORDER_DELIVERY_TYPE_SHIP     = 2;// 海运
    const ORDER_DELIVERY_TYPE_TRAIN    = 3;// 铁运
    const ORDER_DELIVERY_TYPE_TRUCK    = 4;// 汽车
    const ORDER_DELIVERY_TYPE_PEOPLE   = 5;// 人肉

    const VERIFY_STATUS_INIT = 1;//未验货
    const VERIFY_STATUS_DONE = 2;//已验货
    const VERIFY_STATUS_ERR  = 3;//验货有误

    const ORDER_PLAN_DNS    = 0;// 未开始预约
    const ORDER_PLAN_HAS    = 1;// 已预约
    const ORDER_PLAN_CANCEL = 2;// 取消预约


    const ORDER_PAY_STATUS_UNPAY   = 0;// 未支付
    const ORDER_PAY_STATUS_REFUND  = 1;// 退款
    const ORDER_PAY_STAUTS_PAID    = 2;// 支付成功

    const ORDER_PAY_TYPE_ALIPAY  = 1;// 支付宝支付
    const ORDER_PAY_TYPE_WECHAT  = 2;// 微信支付
    const ORDER_PAY_TYPE_BANK  = 3;// 银行卡支付
    const ORDER_PAY_TYPE_CASH  = 4;// 现金支付
    const ORDER_PAY_TYPE_OTHER  = 9;// 其他方式

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
        'delivery_date' => 'date:Y-m-d',
    ];

    protected  $fillable =['warehouse_id','order_type','delivery_date','delivery_type','status','receiver_country','receiver_province','receiver_city','receiver_postcode','receiver_district','receiver_address','receiver_fullname','receiver_phone','send_country','send_province','send_city','send_postcode','send_district','send_address','send_fullname','send_phone','express_num','out_sn','express_code','shop_remark','pay_status','pay_type','sub_pay','payment_account_number','sub_pick_num','sub_pick_num','sale_currency', 'remark','pick_remark', 'share_code'];

    protected $guarded = [];

    protected $appends = [
        'status_name',
        'verify_status_name',
        // 'send_full_address',
        // 'receiver_full_address',
        'currency'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function orderItems()
    {
        return $this->hasMany('App\Models\OrderItem', 'order_id', 'id');
    }

    public function orderLogs()
    {
        return $this->hasMany('App\Models\OrderHistory', 'order_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id');
    }

    public function orderType()
    {
        return $this->belongsTo('App\Models\OrderType', 'order_type', 'id');
    }

    public function userApp()
    {
        return $this->belongsTo('App\Models\UserApp', 'user_app_id', 'id');
    }

    public function operatorUser()
    {
        return $this->belongsTo('App\Models\User', 'operator', 'id');
    }

    // TODO
    // public function pick()
    // {
    //     return $this->hasMany('App\Models\ProductPick', 'shipment_num', 'shipment_num');
    // }

    // 一个出库单对应多个拣货单
    public function picks()
    {
        return $this->hasMany('App\Models\Pick', 'order_id', 'id');
    }

    public function historys()
    {
        return $this->hasMany('App\Models\OrderHistory', 'order_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function getStatusNameAttribute()
    {
        $name = '';

        switch ($this->status) {
            case Order::STATUS_CANCEL:
                $name = "message.orderStatusCancel";
                break;
            case Order::STATUS_DEFAULT:
                $name = "message.orderStatusUnConfirm";
                break;
            case Order::STATUS_PICKING:
               $name = "message.orderStatusPicking";
               break;
            case Order::STATUS_PICK_DONE:
                $name = "message.orderStatusOutbound";
                break;
            case Order::STATUS_WAITING:
               $name = "message.orderStatusUnSend";
               break;
            case Order::STATUS_SENDING:
               $name = "message.orderStatusSending";
               break;
            case Order::STATUS_SUCCESS:
               $name = "message.orderStatusSuccess";
               break;
            default:
                break;
        }

        return trans($name);
    }

    /**
     * @return string
     */
    public function getVerifyStatusNameAttribute()
    {
        $name = '';

        switch ($this->verify_status) {
            case Order::VERIFY_STATUS_INIT:
                $name = "message.orderStatusSuccess";
                break;
            case Order::VERIFY_STATUS_DONE:
                $name = '';
                $name = "message.orderVerifyNone";
                break;
            case Order::VERIFY_STATUS_ERR:
                $name = "message.orderVerifyWrong";
                break;
            default:
                break;
        }

        return trans($name);
    }


    public function getOutSnBarcodeAttribute()
    {
        return 'data:image/png;base64,' . app("DNS1D")->getBarcodePNG($this->out_sn, "C128");
    }


    /**
     * @return boolean
     */
//    public function getRedeliveryStatusAttribute()
//    {
//        return $this->historys()->where('status', Order::STATUS_WAITING)->count() > 1;
//    }



    /**
     * @return boolean
     */
    public function getSendFullAddressAttribute()
    {
        return $this->send_country.$this->send_province.$this->send_city.$this->send_district.$this->send_address;
    }


    /**
     * @return boolean
     */
    public function getReceiverFullAddressAttribute()
    {
        return $this->receiver_country.$this->receiver_province.$this->receiver_city.$this->receiver_district.$this->receiver_address;
    }

    public function getCurrencyAttribute()
    {
        return currency_symbol($this->sale_currency);
    }
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
     * 限制查询只包括指定关键字。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @author liusen
     */
    public function scopeHasKeywords($query, $keywords)
    {
        return $query->where('out_sn', 'like', '%' . $keywords . '%')
                     ->orWhere('express_code', 'like', '%' . $keywords . '%')
                     ->orWhere('express_num', 'like', '%' . $keywords . '%')
                     ->orWhere('receiver_fullname', 'like', '%' . $keywords . '%');
    }

    public function scopeOfWarehouse($query, $warehouse_id)
    {
        return $query->where('warehouse_id', $warehouse_id);
    }


    /*
    |--------------------------------------------------------------------------
    | Operations
    |--------------------------------------------------------------------------
    */

    /**
     * 生成快递单号
     *
     * @param  string $warehouse_code
     * @param  integer $order_id
     * @param  integer $express_code
     *
     * @return string|false
     */
    public static function makeExpressNum($warehouse_code, $order_id, $express_code)
    {
        switch ($express_code) {
            case 'nle':
            case 'express':
            case 'agency':
            case 'today':
            case 'home':
            case 'drop':
                //如果长度只有两位
                if(strlen($warehouse_code) == 2) $warehouse_code = "EU".$warehouse_code;
                return strtoupper($warehouse_code) . encodeData(date('Y-m-d')) . encodeseq($order_id);
            case 'postnl':
                return app('request')->post('express_num');
//                return app('PostnlService')->generateBarcode();
            case 'eax':
                return app('request')->post('express_num');
            default:
                return 'UK' . strtoupper($warehouse_code) . encodeData(date('Y-m-d')) . encodeseq($order_id);
        }
    }

    /**
     * 是否取消预约了
     *
     * @return boolean
     */
    public function isAppointmentCancelled()
    {
        return is_null($this->delivery_date);
    }

    /**
     * 拣货单获取商品详情
     * @param $shipment_num
     * @return bool
     */
    public function shipmentGetOne($shipment_num)
    {
        $data = $this->where('shipment_num', $shipment_num)->with('orderItems.productStock.spec.product')->first();
        return $data ? $data->toArray() : false;
    }

    /**
     * 派送失败
     * @param $id
     * @return bool
     */
    public function expressDefeated($id){
        $update=[
            'is_plan_erp'       =>  0,
            'receiver_country'  =>  '',
            'receiver_city'     =>  '',
            'receiver_postcode' =>  '',
            'receiver_doorno'   =>  '',
            'receiver_address'  =>  '',
            'receiver_fullname' =>  '',
            'receiver_phone'    =>  '',
            'receiver_email'    =>  '',
            'line_name'         =>  '',
            'delivery_date'     => "2017-01-01",
            'old_plan_status'   => 0,
            'status'            => self::STATUS_DEFAULT,
        ];
        return $this->where('id',$id)->update($update);
    }

    /*
    |--------------------------------------------------------------------------
    | 眼码
    |--------------------------------------------------------------------------
    */

    public function toNumber($code)
    {
        return (ord($code[0]) - ord('A')) * 100 + substr($code, 1);
    }

    public function toCode($n)
    {
        $n = $n % (100 * (ord('Z') - ord('A') + 1));
        return chr(floor($n / 100) + ord('A')) . sprintf("%02d", $n % 100);
    }

    public function newMaskCode()
    {
    	$redis_key = 'cms_wms_mask_code';

        if (app('cache')->has($redis_key)) {
            return $this->toCode(app('cache')->increment($redis_key));
        }

        $order = Order::latest()->first();
        $mask_code = $order?$order->mask_code : 0;

        app('cache')->forever($redis_key, $this->toNumber($mask_code));

        return $this->toCode(app('cache')->increment($redis_key));
    }


    public  Static function  generateOutSn()
    {
        $code = uniqid().time().rand(1,100);
        $code = base64_encode($code);
        return strtoupper(substr($code,0,3)).time().rand(1,10);
    }
}
