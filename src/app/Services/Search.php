<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait Search
{
    public static function buildQuery(Builder $query, array $conditions)
    {
        foreach ($conditions as $k => $v) {
            $type = '=';
            $value = $v;
            if (is_array($v) && $k !== 'andQuery') {
                [$type, $value] = $v;
            }
            !is_array($value) ? $value = trim($value) : 1;
            //如果是like搜索，但是值为空，跳过
            if ($type === 'like' && $value === '') {
                continue;
            }
            //in
            if ($type === 'in' && is_array($value)) {
                $query->whereIn($k, $value);
                continue;
            }
            //如果是between， 按时间过滤
            if ($type === 'between' && is_array($value)) {
                if (empty($value[0]) || empty($value[1])) {
                    continue;
                }
                //关联表
                if (strpos($k, ':')) {
                    $k = explode(':', $k);
                    $query->whereHas($k[0], function ($query) use ($k, $value) {
                        if (strpos($value[0], '-') && strpos($value[1], '-')) {
                            $query->whereBetween($k[1], [
                                Carbon::parse($value[0])->startOfDay(),
                                Carbon::parse($value[1])->endOfDay(),
                            ]);
                        } else {
                            $query->whereBetween($k[1], [$value[0], $value[1]]);
                        }
                    });
                    continue;
                }

                //主表过滤
                if (strpos($value[0], '-') && strpos($value[1], '-')) {
                    $query->whereBetween($k, [
                        Carbon::parse($value[0])->startOfDay(),
                        Carbon::parse($value[1])->endOfDay(),
                    ]);
                } else {
                    $query->whereBetween($k, [$value[0], $value[1]]);
                }
                //如果是多个字段联合搜索
            } elseif (strpos($k, ',')) {
                //形如：packages:name,remark;name,value
                if (strpos($k, ':')) {
                    $k = explode(';', $k);
                    $query->where(function ($query) use ($k, $value) {
                        foreach ($k as $key) {
                            //如果是关联搜索
                            if (strpos($key, ':')) {
                                $kk = explode(':', $key);
                                $query->orWhereHas($kk[0], function ($q) use ($kk, $value) {
                                    $q->where(function ($query) use ($kk, $value) {
                                        foreach (explode(',', $kk[1]) as $item) {
                                            $query->orWhere($item, 'like', "%{$value}%");
                                        }
                                    });
                                });
                            } else {
                                $query->orWhere(function ($query) use ($key, $value) {
                                    foreach (explode(',', $key) as $item) {
                                        $query->orWhere($item, 'like', "%{$value}%");
                                    }
                                });
                            }
                        }
                    });
                } else {
                    $query->where(function ($query) use ($k, $value) {
                        foreach (explode(',', $k) as $item) {
                            $query->orWhere($item, 'like', "%{$value}%");
                        }
                    });
                }
                continue;
            } else { //普通类型
                if ($value === '') {
                    continue;
                }
                $query->where($k, $type, $type === 'like' ? "%{$value}%" : $value);
            }
        }
    }
}
