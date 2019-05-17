<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    protected $table = 'category';


    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected  $fillable =['name_cn', 'name_en', 'need_expiration_date', 'need_production_batch_number', 'need_best_before_date', 'is_enabled', 'id', 'owner_id', 'updated_at', 'created_at'];

    protected $hidden = [
        'parent_id',
        'deleted_at',
    ];

    protected  $guarded = [];

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
}
