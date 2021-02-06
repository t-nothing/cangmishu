<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleList extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'cover' => $this->cover ?? '',
            'title' => $this->title,
            'abstract' => $this->abstract ?? '',
            'tag' => $this->tag ?? '',
            'created_at' => (string) $this->created_at,
        ];
    }
}
