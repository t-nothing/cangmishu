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

use Illuminate\Support\Arr;

trait HasBatchOperate
{
    /**
     * @return array
     */
    public function getBatchIds()
    {
        return Arr::wrap(\request()->input('ids', []));
    }

    /**
     * @return array
     */
    public function getBatchDeleteIds()
    {
        return Arr::wrap(\request()->input('DELETE', []));
    }
}
