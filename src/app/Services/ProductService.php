<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace  App\Services;

use App\Models\Product;

class ProductService
{
    public function getProductIdByNameCn($name) {
        $product = Product::where('name_cn', $name)
            ->whose(app('auth')->ownerId())
            ->first();
        if(!$product){
            return 0;
        }
        return $product->id;
    }
}