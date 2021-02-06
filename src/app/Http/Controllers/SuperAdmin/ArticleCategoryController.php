<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
