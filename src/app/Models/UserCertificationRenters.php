<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/4
 * Time: 15:09
 */

namespace App\Models;


class UserCertificationRenters extends Model
{
    const CHECK_STATUS_ONGOING = 1;// 待审核
    const CHECK_STATUS_SUCCESS = 2;// 通过
    const CHECK_STATUS_FAILURE = 3;// 驳回
    const HOME_PAGE_TYPE = 'user_certification_renters';

    protected $table = 'user_certification_renters';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        // 'deleted_at',
        'checked_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
        // 'deleted_at' => 'date:Y-m-d H:i:s',
        'checked_at' => 'date:Y-m-d H:i:s',
    ];

    public function isCheckSucc()
    {
        return $this->status == UserCertification::CHECK_STATUS_SUCCESS;
    }

    public function isCheckFail()
    {
        return $this->status == UserCertification::CHECK_STATUS_FAILURE;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function applicant()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function checkOperatorInfo()
    {
        return $this->belongsTo('App\Models\User', 'check_operator', 'id');
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
        return $query->where('user_id', $user_id);
    }

    /**
     * 限制查询只包括指定关键字。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasKeyword($query, $keywords)
    {
        $user_ids = User::where('email', 'like', '%' . $keywords . '%')->pluck('id');

        return $query->whereIn('user_id', $user_ids);
    }
}
