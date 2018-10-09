<?php

namespace App\Models;

use App\Models\Model;

class Category extends Model
{
    protected $table = 'category';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'parent_id',
        'deleted_at',
    ];

    public function children()
    {
        return $this->hasMany('App\Models\Category', 'parent_id', 'id');
    }

    public function parent()
    {
    	return $this->belongsTo('App\Models\Category', 'parent_id', 'id');
    }

    public function usercategorywarning()
    {
        return $this->belongsTo('App\Models\UserCategoryWarning', 'id', 'category_id')
                    ->where('user_id', app('auth')->id());
    }

    public function userexpirationwarning()
    {
        return $this->belongsTo('App\Models\UserExpirationWarning', 'id', 'category_id')
                    ->where('user_id', app('auth')->id());
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product', 'category_id', 'id');
    }

    public function feature()
    {
        return $this->belongsTo('App\Models\WarehouseFeature', 'warehouse_feature_id', 'id');
    }
}
