<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Privilege;
use App\Rules\PageSize;
use App\Rules\AlphaNumDash;

/**
 * 角色管理
 */
class PrivilegeController extends Controller
{
	/**
     * 列表
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'      => 'integer|min:1',
            'page_size' => new PageSize,
        ]);

        $privileges = Privilege::paginate($request->input('page_size'));

        return formatRet(0, '', $privileges->toArray());
    }

    /**
     * 详情
     */
    public function show($privilege_id)
    {
        if (! $privilege = Privilege::find($privilege_id)) {
            return formatRet(404, '', [], 404);
        }

        return formatRet(0, '', $privilege->toArray());
    }
}
