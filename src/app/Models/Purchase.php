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

class Purchase extends Model
{

    const STATUS_PREPARE    = 1;// 待入库
    const STATUS_PROCEED    = 2;// 入库中
    const STATUS_ACCOMPLISH = 3;// 入库完成
    const STATUS_CANCEL     = 4;// 取消

    protected $table = 'purchase';

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
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'status_name',
    ];

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
        return trans(sprintf("message.batchPurchaseStatus%s", $this->status));
    }

    /**
     * @return string
     */
    public function getPurchaseCodeBarcodeAttribute()
    {
        return 'data:image/png;base64,' . app("DNS1D")->getBarcodePNG($this->batch_code, "C128B");
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany('App\Models\PurchaseItem', 'purchase_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id');
    }

    public function distributor()
    {
        return $this->belongsTo('App\Models\Distributor', 'distributor_id', 'id')->withDefault([
            'name_cn' => '',
            'name_en' => ''
        ]);
    }

    public function operatorUser()
    {
        return $this->belongsTo('App\Models\User', 'operator', 'id')->withDefault([
            'nickname' => '',
        ]);
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
        return $query->where('purchase_code', 'like', '%' . $keywords . '%')
                     ->orWhere('order_invoice_number', 'like', '%' . $keywords . '%');
    }

}
