<?php

namespace App\Models;

/**
 * 官网文章模块
 */
class SAArticle extends Model
{
    protected $table = 'sa_articles';

    protected $guarded = [];

    protected $hidden = [];

    protected $casts = [];

    protected $appends = [];

    /**
     * 分类
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(
            SAArticleCategory::class,
            'category_id',
            'id'
        );
    }
}
