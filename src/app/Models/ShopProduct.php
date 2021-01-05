<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class ShopProduct extends Model
{
    protected $table = 'shop_product';
    protected  $fillable = ['product_id','sale_price','is_shelf','remark','pics'];
    protected $casts = [
        'pics' => 'array',
        'descs'=> 'array',
    ];

    protected $appends = ['name'];

    public function shop()
    {
        return $this->belongsTo('App\Models\Shop', 'shop_id', 'id');
    }

    public function specs()
    {
        return $this->hasMany('App\Models\ShopProductSpec', 'shop_product_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        $name = '';
        $lang = app('translator')->locale()?:'cn';
        if($lang == 'zh-CN'){
            $lang='cn';
        }
        return $this->{'name_'.$lang};
    }

    /**
     * 限制查询只包括指定关键字。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @author liusen
     */
    public function scopeHasKeyword($query, $keywords)
    {
        $query->where('shop_product.name_cn', 'like', '%'.$keywords.'%')
            ->orWhere('shop_product.name_en', 'like', '%'.$keywords.'%')
            ->orWhere('product.barcode', 'like', '%'.$keywords.'%')
            ->orWhereHas('product.specs', function ($q) use ($keywords) {
                $q->where('relevance_code', 'like', '%'.$keywords.'%');
            });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function collectUsers()
    {
        return $this->belongsToMany(
            ShopUser::class,
            'shop_product_collection',
            'shop_product_id',
            'user_id'
        );
    }

    /**
     * 是否收藏
     *
     * @return bool
     */
    public function isCollect()
    {
        $user = auth('shop')->user();

        if ($user) {
            return in_array($user->getKey(), $this->collectUsers->modelKeys());
        }

        return false;
    }
}
