<?php

/**
 * @Author: h9471
 * @Created: 2019/9/10 11:40
 */

namespace App\Services\SuperAdmin;

use App\Models\SAArticle;
use App\Services\BaseService;

class ArticleService extends BaseService
{
    protected $filterRules = [
        'title,content,tag' => ['like', 'keyword'],
    ];

    protected $orderBy = ['id' => 'desc'];

    public function __construct(SAArticle $article)
    {
        $this->request = request();
        $this->formData = $this->request->all();
        $this->model = $article;
        $this->query = $article->newQuery();
        $this->setFilterRules();
    }

    /**
     * @return mixed
     */
    public function publicIndex()
    {
        $this->query->where('status', 1);

        return parent::index();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function publicShow($id)
    {
        return $this->query
            ->where('status', 1)
            ->findOrFail($id);
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
     * 更新信息
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        validator($data, $this->rules())->validate();

        $article = $this->model::findOrFail($id);

        return $article->update([
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'abstract' => mb_substr(strip_tags($data['content']), 0, 150),
                'content' => $data['content'],
                'cover' => $data['thumbnail'] ?? '',
                'tag' => $data['tag'] ?? '',
                'status' => $data['status'] ?? 1,
                'creator' => $data['creator'] ?? 'Admin',
            ]) !== false;
    }

    /**
     * 新建
     * @param array $data
     * @return bool
     */
    public function add(array $data): bool
    {
        validator($data, $this->rules())->validate();

        $article = new $this->model([
            'title' => $data['title'],
            'category_id' => $data['category_id'],
            'abstract' => mb_substr(strip_tags($data['content']), 0, 150),
            'content' => $data['content'],
            'cover' => $data['thumbnail'] ?? '',
            'tag' => $data['tag'] ?? '',
            'status' => $data['status'] ?? 1,
            'creator' => $data['creator'] ?? 'Admin',
        ]);

        return $article->save();
    }

    /**
     * @param array $ids
     * @return bool
     */
    public function publish(array $ids)
    {
        return $this->model::query()->whereKey($ids)->update(['status' => 1]) !== false;
    }

    /**
     * @param array $ids
     * @return bool
     */
    public function unPublish(array $ids)
    {
        return $this->model::query()->whereKey($ids)->update(['status' => 0]) !== false;
    }

    private function rules()
    {
        return [
            'title' => 'required|string|max:50',
            'creator' => 'sometimes|nullable|string',
            'content' => 'required|string',
            'cover' => 'sometimes|nullable|url',
            'tag' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|in:0,1',
        ];
    }
}
