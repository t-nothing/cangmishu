<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/6
 * Time: 16:41
 */

namespace App\Models;


class LeaseApplicationInfo extends Model
{
    protected $table = 'lease_application_info';
    const AUDIT_PENDING = 1;      //待审核
    const AUDIT_PASSED = 2;      //审核通过
    const AUDIT_REJECT = 3;      //审核驳回
    const HOME_PAGE_REN_TYPE = 'lease_application_info_ren';
    const HOME_PAGE_OWN_TYPE = 'lease_application_info_own';
    const HOME_PAGE_TYPE = 'lease_application_info';
    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'check_user_id', 'id');
    }

    public function applicant()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }
}