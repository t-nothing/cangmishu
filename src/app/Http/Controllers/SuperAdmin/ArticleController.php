<?php
/**
 * @Author: h9471
 * @Created: 2020/08/13 11:38
 */

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\HasBatchOperate;
use App\Http\Resources\SuperAdmin\ArticleInfo;
use App\Http\Resources\SuperAdmin\ArticleList as ListResources;
use App\Services\SuperAdmin\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ArticleController extends Controller
{
    use HasBatchOperate;

    protected $service;

    public function __construct(ArticleService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return ArticleInfo::collection($this->service->index())
            ->additional(['ret' => 1, 'msg' => 'success']);
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function publicIndex()
    {
        return ListResources::collection($this->service->index())
            ->additional(['ret' => 1, 'msg' => 'success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ArticleInfo
     */
    public function publicShow(int $id)
    {
        return ArticleInfo::make($this->service->show($id))
            ->additional(['ret' => 1, 'msg' => 'success']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \App\Exceptions\AccidentException
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        if ($this->service->add($request->all())) {
            return formatRet(1, 'success');
        }

        return eRet('failed');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ArticleInfo
     */
    public function show(int $id)
    {
        return ArticleInfo::make($this->service->show($id))
            ->additional(['ret' => 1, 'msg' => 'success']);
    }

    /**
     * 更新
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \Throwable
     */
    public function update(Request $request, int $id)
    {
        if ($this->service->update($id, $request->all())) {
            return formatRet(1, 'success');
        }

        return eRet('failed');
    }

    /**
     * 批量删除
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \App\Exceptions\AccidentException
     */
    public function destroy()
    {
        if ($this->service->delete($this->getBatchIds())) {
            return formatRet(1, 'success');
        }

        return eRet('failed');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    /*public function getCategories()
    {
        return success($this->service->getCategories());
    }*/

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish()
    {
        if ($this->service->publish($this->getBatchIds())) {
            return success();
        }

        return failed();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function unPublish()
    {
        if ($this->service->unPublish($this->getBatchIds())) {
            return success();
        }

        return failed();
    }
}
