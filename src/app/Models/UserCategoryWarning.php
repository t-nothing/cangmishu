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


class UserCategoryWarning extends Model
{
	protected $table = 'user_category_warning';
	protected $fillable = [
		'user_id',
		'category_id',
		'warning_stock'
	];

	public  function category()
    {
        return $this->hasOne(Category::class,'id','category_id');
    }
}
