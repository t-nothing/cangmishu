<?php

/**
 * @Author: h9471
 * @Created: 2019/9/10 11:38
 */

namespace App\Http\Controllers\SuperAdmin;

use App\Services\SuperAdmin\OfficialArticleCategoryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ArticleCategoryController extends Controller
{
    protected $service;

    public function __construct(OfficialArticleCategoryService $category)
    {
        $this->service = $category;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return success($this->service->getList());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if ($this->service->create($request->all())) {
            return success();
        }

        return failed();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return mixed
     */
    public function show(int $id)
    {
        return $this->service->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \App\Exceptions\AccidentException
     */
    public function update(Request $request, $id)
    {
        if ($this->service->update($id, $request->all())) {
            return formatRet(1, 'success');
        }

        return eRet('failed');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        if ($this->service->destroy($id)) {
            return success();
        }

        return failed();
    }
}
