<?php

namespace App\Http\Controllers;

use App\Models\CertificationContract;
use Illuminate\Http\Request;
use App\Models\UserCertificationOwner;
use App\Models\UserCertificationRenters;

class UserCertificationController extends Controller
{
    /**
     * 用户申请认证（仓库创建认证）
     */
    public function ownerApply(Request $request)
    {
        $this->validate($request, [
            'warehouse_name_cn' => 'required|string|max:255',
            'warehouse_name_en' => 'required|string|max:255',
            'warehouse_property' => 'required|string|max:255',
            'phone_codes' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'postcode' => 'required|string|max:255',
            'door_no' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'warehouse_plan' => 'url',
            'contract' => 'array',//required|暂时更新不需要必填
            'contract.*.pdf_name' => 'string|max:255',
            'contract.*.pdf_url' => 'url|max:255',
        ]);

        $exist = UserCertificationOwner::where('user_id', app('auth')->id())->where(function ($query) {
            $query->where('status', UserCertificationOwner::CHECK_STATUS_SUCCESS)
                ->orWhere('status', UserCertificationOwner::CHECK_STATUS_ONGOING);
        })->first();
        if ($exist) {
            return formatRet(1, '用户已经提交申请认证信息');
        }
        $certification = new UserCertificationOwner();
        $certification->user_id = app('auth')->guard()->user()->getAuthIdentifier();
        $certification->status = 1;
        $certification->warehouse_name_cn = $request->warehouse_name_cn;
        $certification->warehouse_name_en = $request->warehouse_name_en;
        $certification->warehouse_property = $request->warehouse_property;
        $certification->phone_codes = $request->phone_codes;
        $certification->phone = $request->phone;
        $certification->country = $request->country;
        $certification->postcode = $request->postcode;
        $certification->door_no = $request->door_no;
        $certification->city = $request->city;
        $certification->street = $request->street;
        $certification->warehouse_plan = $request->input('warehouse_plan');
        if (!$certification->save()) {
            return formatRet(2, '用户提交认证信息失败');
        }

        if ($request->filled('contract')) {//保存合同路径 暂时非必填
            $contract = [];
            foreach ($request->contract as $k => $v) {
                if ($v['pdf_name'] && $v['pdf_url']) {
                    $contract[] = [
                        'certification_owner_id' => $certification->id,
                        'pdf_name' => $v['pdf_name'],
                        'pdf_url' => $v['pdf_url'],
                        'created_at' => time(),
                    ];
                }
            }
            if (!empty($contract)) {
                CertificationContract::insert($contract);
            }
        }
        return formatRet(0, '用户提交认证信息成功');
    }

    /**
     * 用户申请认证（仓库租赁认证）
     */
    public function rentersApply(Request $request)
    {
        $this->validate($request, [
            'company_name_cn' => 'required|string|max:255',
            'company_name_en' => 'required|string|max:255',
            //   'warehouse_owner' => 'required|string|max:255',
            'phone_codes' => 'required|string|max:255',
            'kvk_code' => 'required|string|max:255',
            'vat_code' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'postcode' => 'required|string|max:255',
            'door_no' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
        ]);
        $exist = UserCertificationRenters::where('user_id', app('auth')->id())->where(function ($query){
            $query->where('status',UserCertificationRenters::CHECK_STATUS_ONGOING)
                ->orWhere('status',UserCertificationRenters::CHECK_STATUS_SUCCESS);
        })->first();
        if ($exist) {
            return formatRet(1, '用户已经提交租赁认证');
        }
        $certificationRenters = new UserCertificationRenters();
        $certificationRenters->user_id = app('auth')->guard()->user()->getAuthIdentifier();
        $certificationRenters->company_name_cn = $request->company_name_cn;
        $certificationRenters->company_name_en = $request->company_name_en;
        $certificationRenters->status = 1;
        //  $certificationRenters->warehouse_owner = $request->warehouse_owner;
        $certificationRenters->kvk_code = $request->kvk_code;
        $certificationRenters->vat_code = $request->vat_code;
        $certificationRenters->phone = $request->phone;
        $certificationRenters->country = $request->country;
        $certificationRenters->postcode = $request->postcode;
        $certificationRenters->door_no = $request->door_no;
        $certificationRenters->city = $request->city;
        $certificationRenters->street = $request->street;
        if (!$certificationRenters->save()) {
            return formatRet(2, '用户提交失败');
        }
        return formatRet(0, '用户提交成功');
    }
}
