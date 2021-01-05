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

    /**
     * 得到一个有效的FORM ID
     */
    public static function getOne($userId){
        $info =  Self::where('user_id', $userId)->where('is_used', Self::STATUS_UNUSE)->latest()->first();
        if(!$info) {
            return "";
        }
        $info->is_used = Self::STATUS_USED;
        // $info->times += $info->times;
        $info->save();
        return $info->form_id;
    }
}
