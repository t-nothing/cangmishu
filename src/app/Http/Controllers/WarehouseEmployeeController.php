<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\UserEmployee;
use App\Models\Warehouse;
use App\Models\WarehouseEmployee;
use App\Rules\PageSize;
use Illuminate\Support\Carbon;

class WarehouseEmployeeController extends Controller
{
    /**
     * 仓库员工列表
     */
    public function list(Request $request, $warehouse_id)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'role_id'      => 'integer|min:1',
        ]);

        $warehouseEmployee = WarehouseEmployee::with('employee')
                                      ->ofWarehouse($warehouse_id)
                                      ->distinct();

        if ($request->filled('role_id')) {
            $warehouseEmployee->where('role_id', $request->role_id);
        }

        $employees = $warehouseEmployee->latest()->paginate($request->input('page_size'));

        return formatRet(0, '', $employees->toArray());
    }

    /**
     * 添加员工到仓库员工列表
     */
    public function create(Request $request, $warehouse_id)
    {
        $this->validate($request, [
            'user_id'   => 'required|array',
            'user_id.*' => 'required|integer|min:1|distinct',
            'role_id'   => 'required|integer|min:1',
        ]);

        if (! $warehouse = Warehouse::find($warehouse_id)) {
            return formatRet(404, '找不到该仓库');
        }

        if (app('auth')->id() != $warehouse->user_id) {
            return formatRet(403, '无权限使用该仓库');
        }

        if (! $role = Role::find($request->role_id)) {
            return formatRet(404, '找不到该仓库角色');
        }

        if ($role->type != 3) {
            return formatRet(404, '找不到该仓库角色');
        }

        foreach ($request->user_id as $id) {
            if (! $user = User::find($id)) {
                return formatRet(404, '找不到该用户');
            }

            // 请先将该员工加到我的员工列表
            $userEmployee = UserEmployee::where('user_id', $id)->where('boss_id', app('auth')->id())->first();
            if (! $userEmployee) {
                return formatRet(404, '在员工列表中找不到该员工');
            }

            // 不能重复添加
            $exist = WarehouseEmployee::where('warehouse_id', $warehouse_id)
                                      ->where('user_id', $request->user_id)
                                      ->where('role_id', $request->role_id)
                                      ->first();

            if ($exist) {
                return formatRet(500, '已添加该用户，请勿重复操作');
            }

            $data[] = [
                'warehouse_id' => $warehouse_id,
                'user_id'      => $id,
                'role_id'      => $request->role_id,
                'created_at'   => new Carbon,
            ];
        }

        if (! WarehouseEmployee::insert($data)) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }

    /**
     * 移除员工出仓库员工列表
     */
    public function delete(Request $request, $warehouse_id)
    {
        $this->validate($request, [
            'id' => 'required|integer|min:1',
        ]);

        $boss_id = app('auth')->id();

        if (! $warehouse = Warehouse::find($warehouse_id)) {
            return formatRet(404, '找不到该仓库');
        }

        if (app('auth')->id() != $warehouse->user_id) {
            return formatRet(403, '无权限使用该仓库');
        }

        $warehouseEmployee = WarehouseEmployee::find($request->id);

        if (! $warehouseEmployee) {
            return formatRet(404, '在仓库员工列表中找不到该员工');
        }

        if ($warehouseEmployee->warehouse_id != $warehouse_id) {
            return formatRet(404, '在仓库员工列表中找不到该员工');
        }

        if (! $warehouseEmployee->delete()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }
}
