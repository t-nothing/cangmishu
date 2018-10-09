<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\WarehouseEmployee;
use App\Rules\PageSize;

class WarehouseEmployeeRoleController extends Controller
{
    /**
     * 仓库员工组列表
     */
    public function list(Request $request, $warehouse_id)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
        ]);

        $roles = Role::addSelect(['id', 'name_cn', 'name_en'])->withCount(['employees' => function ($query) use ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        }])->where('type', 3)->paginate($request->input('page_size'));

        return formatRet(0, '', $roles->toArray());
    }
}
