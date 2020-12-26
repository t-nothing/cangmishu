<?php

namespace App\Models;

/**
 * 官网文章分类
 */
class SAArticleCategory extends Model
{
    protected $table = 'sa_article_categories';

    protected $guarded = [];

    protected $hidden = [];

    protected $casts = [];

    protected $appends = [];

    /**
     * 分类
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function articles()
    {
        return $this->hasMany(SAArticle::class, 'category_id', 'id');
    }
}
