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


class HomePageNotice extends Model
{
    const DISPLAY = 1;
    const HIDE = 0;

    protected $table = 'home_page_notice';

    protected $dates = [
        'created_at',
        'updated_at',
        'notice_time',
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
        'notice_time' => 'date:Y-m-d H:i:s',
    ];

    protected $fillable = [
        'notice_type',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

}