<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Models\Distributor;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DistributorController extends Controller
{
    /**
     * 供应商 - 列表
     *
     * @author liusen
     */
    public function index(BaseRequests $request)
    {
        $this->validate($request, [

            'page'      => 'integer|min:1',
            'page_size' => new PageSize(),
            'keywords'  => 'sometimes|string',
            'all' =>'sometimes|integer'
        ]);

        $distributors = Distributor::where('user_id',app('auth')->ownerId())
                        ->when($request->filled('keywords'),function($q) use ($request){
                            return $q->hasKeywords($request->keywords);
                        });
        if($request->input('all',0) == 0){
            $distributors = $distributors ->paginate($request->input('page_size',10));
        }else{
            $distributors = $distributors ->get();
        }

        return formatRet(0, '', $distributors->toArray());
    }

    /**
     * 供应商 - 创建
     *
     * @author liusen
     */
    public function store(BaseRequests $request)
    {
        $this->validate($request, [
            'name_cn' => [
                'required','string','max:50',
                Rule::unique('distributor')->where(function ($query) {
                    return $query->where('user_id',Auth::ownerId());
                }),
            ],
            'city'      => 'sometimes|string',
            'country'   => 'sometimes|string',
            'door_no'   => 'sometimes|string',
            'email'     => 'sometimes|string',
            'phone'     => 'sometimes|string',
            'postcode'  => 'sometimes|string',
            'street'    => 'sometimes|string',
            'address'   => 'sometimes|string',
            'province'   => 'sometimes|string',
            'district'   => 'sometimes|string',
        ]);

        $distributor = new Distributor;
        $distributor->user_id = Auth::id();
        $distributor->name_cn = $request->name_cn;
        $distributor->name_en = $request->name_en??$request->name_cn;
        $distributor->country      = $request->country??'中国';
        $distributor->city      = $request->city??'';
        $distributor->door_no   = $request->door_no??'';
        $distributor->email   = $request->email??'';
        $distributor->phone   = $request->phone??'';
        $distributor->street   = $request->street??'';
        $distributor->address   = $request->address??'';
        $distributor->province   = $request->province??'';
        $distributor->district   = $request->district??'';

        if ($distributor->save()) {
            return formatRet(0,'', $distributor->toArray());
        }

        return formatRet(500, trans("message.distributorAddFailed"));
    }

    /**
     * 供应商 - 修改
     *
     * @author liusen
     */
    public function update(BaseRequests $request, $distributor_id)
    {
        $this->validate($request, [
            'name_cn'        => [
                'required','string','max:50',
                Rule::unique('distributor')->where(function ($query)use($distributor_id) {
                    return $query->where('user_id',Auth::ownerId());
                })
                ->ignore($distributor_id)
            ],
            'city'      => 'sometimes|string',
            'country'   => 'sometimes|string',
            'door_no'   => 'sometimes|string',
            'email'     => 'sometimes|string',
            'phone'     => 'sometimes|string',
            'postcode'  => 'sometimes|string',
            'street'    => 'sometimes|string',
            'address'   => 'sometimes|string',
            'province'   => 'sometimes|string',
            'district'   => 'sometimes|string',
        ]);

        if (! $distributor = Distributor::find($distributor_id)) {
            return formatRet(404, trans("message.distributorNotExist"), [], 404);
        }

        $distributor->name_cn   = $request->name_cn;
        $distributor->name_en   = $request->name_en??$request->name_cn;
        $distributor->country      = $request->country??'中国';
        $distributor->city      = $request->city??'';
        $distributor->door_no   = $request->door_no??'';
        $distributor->email   = $request->email??'';
        $distributor->phone   = $request->phone??'';
        $distributor->street   = $request->street??'';
        $distributor->address   = $request->address??'';
        $distributor->province   = $request->province??'';
        $distributor->district   = $request->district??'';

        if ($distributor->save()) {
            return formatRet(0);
        }
        return formatRet(500, trans("message.distributorUpdateFailed"));
    }

    /**
     * 供应商 - 删除
     *
     * @author liusen
     */
    public function destroy( $distributor_id)
    {
        if (! $distributor = Distributor::whose(Auth::id())->find($distributor_id)) {
            return formatRet(404, trans("message.distributorNotExist"), [], 404);
        }

        if ($distributor->delete()) {
            return formatRet(0);
        }
        return formatRet(500, trans("message.distributorDeleteFailed"));
    }

    /**
     * 供应商 - 查看
     *
     * @author liusen
     */
    public function show( BaseRequests $request,$id)
    {
        $id = intval($id);
        $distributor = Distributor::whose(Auth::id())->find($id);
        if(!$distributor){
            return formatRet(404, trans("message.distributorNotExist"), [], 404);
        }
        

        return formatRet(0, '', $distributor->toArray());
       
    }
}
