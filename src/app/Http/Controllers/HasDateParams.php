<?php
/**
 * @Author: h9471
 * @Created: 2020/11/4 14:21
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
