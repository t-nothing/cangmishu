<?php

/**
 * @Author: h9471
 * @DateTime: 2020/11/13 10:52
 */

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleInfo extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'cover' => $this->cover ?? '',
            'title' => $this->title,
            'abstract' => $this->abstract ?? '',
            'content' => $this->content ?? '',
            'tag' => $this->tag ?? '',
            'status' => $this->status,
            'created_at' => (string) $this->created_at,
        ];
    }
}
