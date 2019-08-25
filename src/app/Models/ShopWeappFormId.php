<?php

namespace App\Models;


class ShopWeappFormId extends Model
{

    protected $table = 'shop_weapp_form_id';

    const STATUS_UNUSE    = 0;// 未使用
    const STATUS_USED     = 1;// 已使用

    protected $hidden = [
        'parent_id',
        'deleted_at',
    ];

    protected  $fillable = ['form_id','is_used','user_id'];

    protected  $guarded = [];
    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo('App\Models\ShopUser', 'user_id', 'id');
    }


    public  function ScopeOfWhose($query,$user_id)
    {
        return $query->where('user_id',$user_id);
    }
}
