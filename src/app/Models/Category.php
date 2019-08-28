<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;

class Category extends Model
{
    protected $table = 'category';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected  $fillable =['name_cn', 'name_en', 'need_expiration_date', 'need_production_batch_number', 'need_best_before_date', 'is_enabled', 'owner_id', 'updated_at', 'created_at','warehouse_id'];

    protected $hidden = [
        'parent_id',
        'deleted_at',
    ];

    protected  $guarded = [];

    public  $appends = [
        'need_expiration_date_name',
        'need_best_before_date_name',
        'need_production_batch_number_name'
    ];

    public function products()
    {
        return $this->hasMany('App\Models\Product', 'category_id', 'id');
    }

    public function feature()
    {
        return $this->belongsTo('App\Models\WarehouseFeature', 'warehouse_feature_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

    public  function ScopeOfWhose($query,$owner_id)
    {
        return $query->where('owner_id', $owner_id);
    }

    public  function getNeedExpirationDateNameAttribute()
    {
        return $this->need_expiration_date?"保质期":"";
    }


    public  function getNeedBestBeforeDateNameAttribute()
    {
        return $this->need_best_before_date?"最佳使用期":"";

    }

    public  function getNeedProductionBatchNumberNameAttribute()
    {
        return $this->need_production_batch_number?"生产批次号":"";
    }

}
