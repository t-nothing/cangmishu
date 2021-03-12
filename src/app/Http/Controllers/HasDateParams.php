<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers;

trait HasDateParams
{
    public function getRequestParams()
    {
        $days = \request()->input('days', 1);

        $begin = \request()->input('begin', '');
        $end = \request()->input('end', '');

        if ($begin && $end) {
            return [$begin, $end];
        }

        return $days;
    }
}