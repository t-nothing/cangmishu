<?php

/**
 * @Author: h9471
 * @Created: 2019/9/10 14:12
 */

namespace App\Services;

use App\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class BaseService
{
    use Search;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Builder $query
     */
    protected $query;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array 过滤
     */
    protected $filters = [];

    /**
     * @var array 过滤规则
     */
    protected $filterRules = [];

    /**
     * @var array 排序规则
     */
    protected $orderBy = [];

    /**
     * @var array 表单数据
     */
    protected $formData = [];

    protected static $relation = [];

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @return $this
     */
    public function setFilter()
    {
        if ($this->filters) {
            static::buildQuery($this->query, $this->filters);
        }
        return $this;
    }

    public function setFilterRules()
    {
        foreach ($this->filterRules as $k => $v) {
            if (Arr::has($this->formData, $v[1])) {
                //获取操作符
                $this->filters[$k][0] = $v[0];
                //获取值
                if (is_array($v[1])) {
                    $this->filters[$k][1] = [];
                    foreach ($v[1] as $v1) {
                        array_push($this->filters[$k][1], $this->formData[$v1]);
                    }
                } else {
                    $this->filters[$k][1] = $this->formData[$v[1]];
                }
            }
        }
        return true;
    }

    /**
     * @return $this
     */
    public function setOrderBy()
    {
        if ($this->orderBy) {
            foreach ($this->orderBy as $key => $value) {
                $this->query->orderBy($key, $value);
            }
        }
        return $this;
    }

    public function pageSize()
    {
        $size = request()->input('size', 10);
        $page = request()->input('size', 1);

        return $this->query->paginate($size, ['*'], 'page', $page);
    }

    /**
     * 详细
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        return $this->model::findOrFail($id);
    }

    /**
     * 列表
     * @return mixed
     */
    public function index()
    {
        return $this->setFilter()->setOrderBy()->pageSize();
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->query->get();
    }

    /**
     * 删除
     * @param array $id
     * @return bool
     */
    public function delete(array $id): bool
    {
        return $this->model::whereIn('id', $id)->delete();
    }

    /**
     * 获取某个字段数据
     *
     * @param  string  $column
     * @param  int  $limit
     * @return Collection
     */
    public function getColumnData(string $column, int $limit = 5)
    {
        if (in_array($column, $this->model->searchable)) {
            if (mb_strpos($column, '.')) {
                [$relation, $column] = explode('.', $column);
                //可能需要换个名字
                if (array_key_exists($relation, static::$relation)) {
                    $relation = static::$relation[$relation];
                }

                $query = $this->model::with($relation . ':' . $column)
                    ->get()
                    ->pluck($relation)
                    ->flatten()
                    ->unique(function ($model) use ($column) {
                        return $model->$column;
                    });
            } else {
                $query = $this->model::select([$this->model->getQualifiedKeyName(), $column])
                    ->get()
                    ->unique(function ($model) use ($column) {
                        return $model->$column;
                    });
            }
            //数值转换，可能需要更加优雅的方式解决
            return $query->map(function ($model) use ($column) {
                if (array_key_exists($column, static::$casts)) {
                    return [$column => $model->$column / static::$casts[$column]];
                }

                return [$column => $model->$column];
            })->sortBy(function ($value) use ($column) {
                return $value[$column];
            })->values();
        }

        return collect([]);
    }

    /**
     * 自定义过滤
     *
     * @return bool
     */
    protected function queryParser()
    {
        $queries = $this->formData['queries'] ?? '';

        if ($queries === '') {
            return false;
        }

        $queriesData = explode('|', $queries);

        foreach ($queriesData as $q) {
            [$column, $values] = explode(':', $q);

            [$searches, $order] = explode(';', $values);

            if (array_key_exists($column, static::$casts)) {
                $searches = collect(explode(',', $searches))->map(function ($value) use ($column) {
                    return $value * static::$casts[$column];
                })->join(',');
            }

            if (!mb_strpos($column, '.') && Schema::hasColumn($this->model->getTable(), $column)) {
                //主表搜索
                $this->query->where(function ($query) use ($column, $searches) {
                    $searches = explode(',', $searches);
                    foreach ($searches as $search) {
                        //翻译字段，是JSON，只搜索中文部分
                        if (in_array($column, $this->model->translatable)) {
                            $query->orWhere($column . '->zh_CN', $search);
                        } else {    //其他的直接匹配字段
                            $query->orWhere($column, $search);
                        }
                    }
                });
                //主表排序
                if ($order) {
                    $this->query->orderBy($column, $order ?? 'asc');
                }
            } elseif (mb_strpos($column, '.')) {
                //如果是模型关联属性搜索，且能找到这个关联
                [$relation, $column] = explode('.', $column);
                //关联搜索
                if (method_exists($this->model, $relation)) {
                    $this->query->whereHas($relation, function ($query) use ($column, $searches) {
                        $searches = explode(',', $searches);
                        $query->where(function ($query) use ($column, $searches) {
                            foreach ($searches as $search) {
                                $query->orWhere($column, $search);
                            }
                        });
                    });
                } else {

                    //如果是模型关联属性搜索，不能找到这个关联
                    //可能字段名和关联名并不相同
                    //需要自定义
                    if (array_key_exists($relation, static::$relation)) {
                        $relation = static::$relation[$relation];

                        $this->query->whereHas($relation, function ($query) use ($column, $searches) {
                            $searches = explode(',', $searches);
                            $query->where(function ($query) use ($column, $searches) {
                                foreach ($searches as $search) {
                                    $query->orWhere($column, $search);
                                }
                            });
                        });
                    }
                }
            }
        }

        return true;
    }

    protected function translateRules()
    {
        //TODO: Maybe need a rule to validate language
        return [
            'language' => 'required|string',
        ];
    }
}
