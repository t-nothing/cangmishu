<?php

namespace App\Models;

class Batch extends Model
{

    const STATUS_PREPARE    = 1;// 待入库
    const STATUS_PROCEED    = 2;// 入库中
    const STATUS_ACCOMPLISH = 3;// 入库完成
    const STATUS_CANCEL     = 4;// 取消

    const TRANSPORTATION_TYPE_SHIP    = 1;// 海运
    const TRANSPORTATION_TYPE_AIR     = 2;// 空运
    const TRANSPORTATION_TYPE_EXPRESS = 3;// 快递
    const TRANSPORTATION_TYPE_DIY     = 4;// 自送
    
    protected $table = 'batch';

    protected  $fillable = ['type_id','warehouse_id','batch_code','plan_time','over_time','distributor_id','remark','confirmation_number','owner_id','status'];

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

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'status_name',
        'total_num',
        'transportation_type_name',
    ];

    protected $status_translations = [
        Batch::STATUS_PREPARE => [
            'cn' => '待入库',
            'en' => 'waiting',
        ],
        Batch::STATUS_PROCEED => [
            'cn' => '入库中',
            'en' => 'ongoing',
        ],
        Batch::STATUS_ACCOMPLISH => [
            'cn' => '入库完成',
            'en' => 'success',
        ],
        Batch::STATUS_CANCEL => [
            'cn' => '取消',
            'en' => 'cancelled',
        ],
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
        return $this->translateStatusTo($this->status);
    }

    /**
     * @return string
     */
    public function getBatchCodeBarcodeAttribute()
    {
        return 'data:image/png;base64,' . app("DNS1D")->getBarcodePNG($this->batch_code, "C128");
    }

    /**
     * @return array
     */
    public function getTotalNumAttribute()
    {
        $r = [
            'total_need_num' => 0,
            'total_stockin_num' => 0,
        ];

        $r['total_need_num']    = $this->batchProducts->sum('need_num');
        $r['total_stockin_num'] =  $this->batchProducts->sum('total_stockin_num');

        return $r;
    }

    /**
     * @return string
     * @todo   需要更优雅地实现多语言
     */
    public function getTransportationTypeNameAttribute()
    {
        $k = $this->transportation_type;
        $lang = 'cn';

        $translations = [
            Batch::TRANSPORTATION_TYPE_SHIP => [
                'cn' => '海运',
                'en' => 'ship',
            ],
            Batch::TRANSPORTATION_TYPE_AIR => [
                'cn' => '空运',
                'en' => 'air',
            ],
            Batch::TRANSPORTATION_TYPE_EXPRESS => [
                'cn' => '快递',
                'en' => 'express',
            ],
            Batch::TRANSPORTATION_TYPE_DIY => [
                'cn' => '自送',
                'en' => 'DIY',
            ],
        ];

        return isset($translations[$k][$lang])
            ? $translations[$k][$lang]
            : '';
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function stocks()
    {
        return $this->hasMany('App\Models\ProductStock', 'batch_id', 'id');
    }

    public function batchProducts()
    {
        return $this->hasMany('App\Models\ProductStock', 'batch_id', 'id')
                    ->with('spec.product');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id');
    }

    public function batchType()
    {
        return $this->belongsTo('App\Models\BatchType', 'type_id', 'id');
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
        return $query->where('batch_code', 'like', '%' . $keywords . '%')
                     ->orWhere('confirmation_number', 'like', '%' . $keywords . '%');
    }

    /*
    |--------------------------------------------------------------------------
    | Operations
    |--------------------------------------------------------------------------
    */

    /**
     * 是否可以继续入库。
     *
     * @return bool
     * @author liusen
     */
    public function canStockIn()
    {
        return isset($this->status) && in_array($this->status, [
            Batch::STATUS_PREPARE,
            Batch::STATUS_PROCEED,
        ]);
    }

    /**
     * 翻译
     *
     * @return string
     */
    public function translateStatusTo($status, $lang = 'cn')
    {
        return isset($this->status_translations[$status][$lang])
            ? $this->status_translations[$status][$lang]
            : '';
    }

    // 生成和比对SKU
    // public static function generateSKU(ProductStock $stock)
    // {
    //     if (! empty($stock->sku)) {
    //         return $stock->sku;
    //     }

    //     $batch = $stock->batch;

    //     $sku = ProductSku::where('spec_id', $stock->spec->id)
    //                      ->where('expiration_date', $stock->expiration_date)
    //                      ->where('production_batch_number', $stock->production_batch_number)
    //                      ->where('confirmation_number', $stock->batch->confirmation_number)
    //                      ->value('sku');

    //     if (! $sku) {
    //         $sku = 'sku' . get_hash();

    //         DB::transaction(function () use ($stock, $sku) {
    //             $exists = ProductSku::where('sku', $sku)->first();

    //             if (! $exists) {
    //                 $productSku = ProductSku::insert([
    //                     'sku'                      => $sku,
    //                     'spec_id'                  => $stock->spec_id,
    //                     'expiration_date'          => $stock->expiration_date,
    //                     'production_batch_number'  => $stock->production_batch_number,
    //                     'confirmation_number'      => $stock->batch->confirmation_number,
    //                     'batch_code'               => $stock->batch->batch_code,
    //                 ]);

    //                 $stock->sku = $sku;
    //                 $stock->save();
    //             }
    //         });
    //     }

    //     return false;
    // }

}