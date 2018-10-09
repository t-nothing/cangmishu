<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Rules\PageSize;
use App\Rules\AlphaNumDash;
use App\Rules\NameCn;
use App\Rules\NameEn;
use App\Models\Warehouse;
use App\Models\WarehouseArea;
use App\Models\WarehouseEmployee;
use App\Models\LeaseApplicationInfo;
use App\Models\UserExtra;

/**
 * 仓库管理
 */
class WarehouseController extends Controller
{
    /**
     * 仓库 - 列表
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page' => 'integer|min:1',
            'page_size' => new PageSize,
            'type' => 'integer|in:1,2',
            'keywords' => 'string',
            'from' => 'integer|min:1',// 1:自己私有仓库或租用别人公共仓库 or 仓库管理列表
        ]);

        $user_id = Auth::id();

        if ($request->filled('from') && $request->input('from') == 1) {
            // 自己私有仓库、租用别人公共仓库、作为员工可以使用的仓库
            $warehouse_ids = WarehouseEmployee::distinct()->where('user_id', $user_id)->pluck('warehouse_id');

            $warehouses = Warehouse::ofUser($user_id)
                ->orWhereIn('id', $warehouse_ids)
                ->latest()
                ->paginate($request->input('page_size'));
        } else {
            // 仓库管理列表
            $warehouse = Warehouse::latest()->whose($user_id);

            if ($request->filled('type')) {
                $warehouse = $warehouse->where('type', $request->type);
            }

            if ($request->filled('keywords')) {
                $warehouse = $warehouse->hasKeyword($request->keywords);
            }

            $warehouses = $warehouse->paginate($request->input('page_size'));
        }

        return formatRet(0, '', $warehouses->toArray());
    }

    /**
     * 仓库 - 新增
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'name_cn' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'type' => 'required|integer|in:1,2',
            'area' => 'required|numeric',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'door_no' => 'required|string|max:10',
            'postcode' => 'required|string|max:7',
            'contact_user' => 'required|string|max:20',
            'contact_number' => 'required|string|max:20',
            'contact_email' => 'required|email',
            'status' => 'required|boolean',
            'logo_photo' => 'url',
        ]);

        $user = app('auth')->user();

        if (!$user->extra || $user->extra->is_certificated_creator == 0) {
            return formatRet(403, '请先通过创建仓库的认证');
        }

        switch ($request->type) {
            case Warehouse::TYPE_OPEN:
                if ($user->extra->isShareMax()) {
                    return formatRet(500, '拥有的公用仓库数已达上限');
                }
                break;
            case Warehouse::TYPE_PERSONAL:
                if ($user->extra->isSelfMax()) {
                    return formatRet(500, '拥有的自用仓库数已达上限');
                }
                break;
            default:
                # code...
                break;
        }

        $warehouse = new Warehouse;

        $this->validate($request, [
            'name_cn' => [new NameCn, Rule::unique($warehouse->getTable())],
            'name_en' => [new NameEn, Rule::unique($warehouse->getTable())],
            'code' => [new AlphaNumDash, Rule::unique($warehouse->getTable())],
        ]);

        $warehouse->owner_id = Auth::id();
        $warehouse->name_cn = $request->name_cn;
        $warehouse->name_en = $request->name_en;
        $warehouse->code = $request->code;
        $warehouse->type = $request->type;
        $warehouse->area = $request->area;
        $warehouse->country = $request->country;
        $warehouse->city = $request->city;
        $warehouse->street = $request->street;
        $warehouse->door_no = $request->door_no;
        $warehouse->postcode = $request->postcode;
        $warehouse->contact_user = $request->contact_user;
        $warehouse->contact_number = $request->contact_number;
        $warehouse->contact_email = $request->contact_email;
        $warehouse->status = $request->status;
        $warehouse->logo_photo = $request->input('logo_photo');
        $warehouse->apply_num = 0;

        // 仓库被创建时，如果是公共，则无使用者；如果是私有，则是创建者
        if ($request->type == Warehouse::TYPE_PERSONAL) {
            $warehouse->is_used = 1;
            $warehouse->user_id = Auth::id();
        } else {
            $warehouse->is_used = 0;
            $warehouse->user_id = 0;
        }

        if (!$warehouse->save()) {
            return formatRet(500, '失败');
        }
        //添加仓库权限
        if( ! $warehouse::addEmployee(WarehouseEmployee::ROLE_OWNER,$warehouse->owner_id,$warehouse->id)){
            return formatRet(500, '给用户添加仓库权限失败');
        }
        return formatRet(0, '');
    }

    /**
     * 仓库 - 修改
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'warehouse_id' => 'required|integer|min:1',
            'name_cn' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'area' => 'required|numeric',
            'country' => 'required|string|max:255',
            'door_no' => 'required|string|max:10',
            'postcode' => 'required|string|max:7',
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'contact_user' => 'required|string|max:20',
            'contact_number' => 'required|string|max:20',
            'contact_email' => 'required|email',
            'status' => 'required|boolean',
            'logo_photo' => 'url',
        ]);

        if (!$warehouse = Warehouse::find($request->warehouse_id)) {
            return formatRet(404, '仓库不存在或已被删除', [], 404);
        }

        $this->validate($request, [
            'name_cn' => [new NameCn, Rule::unique($warehouse->getTable())->ignore($warehouse->id)],
            'name_en' => [new NameEn, Rule::unique($warehouse->getTable())->ignore($warehouse->id)],
            'code' => [new AlphaNumDash, Rule::unique($warehouse->getTable())->ignore($warehouse->id)],
        ]);

        // 更新
        $warehouse->name_cn = $request->name_cn;
        $warehouse->name_en = $request->name_en;
        $warehouse->code = $request->code;

        if (!$warehouse->isUsed()) {
            $this->validate($request, [
                'type' => 'required|integer|in:1,2',
                'status' => 'required|boolean',
            ]);

            $warehouse->type = $request->type;
            $warehouse->status = $request->status;
        }

        $warehouse->area = $request->area;
        $warehouse->country = $request->country;
        $warehouse->city = $request->city;
        $warehouse->street = $request->street;
        $warehouse->door_no = $request->door_no;
        $warehouse->postcode = $request->postcode;
        $warehouse->contact_user = $request->contact_user;
        $warehouse->contact_number = $request->contact_number;
        $warehouse->contact_email = $request->contact_email;
        $warehouse->status = $request->status;
        $warehouse->logo_photo = $request->input('logo_photo');

        if ($warehouse->save()) {
            return formatRet(0, '');
        }

        return formatRet(500, '失败');
    }

    /**
     * 仓库 - 详情
     */
    public function show($warehouse_id)
    {
        if ($warehouse = Warehouse::find($warehouse_id)) {
            return formatRet(0, '', $warehouse->toArray());
        }

        return formatRet(404, '', [], 404);
    }
}
