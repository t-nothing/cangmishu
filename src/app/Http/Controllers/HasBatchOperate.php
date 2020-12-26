<?php
/**
 * @Author: h9471
 * @Created: 2020/3/10 14:19
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
