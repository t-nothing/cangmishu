<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CertificationNoticeMail;
use Illuminate\Http\Request;
use App\Rules\PageSize;
use App\Models\UserExtra;
use App\Models\UserCertificationRenters;
use Illuminate\Support\Facades\Mail;

class UserRenterCertificationApplicationController extends Controller
{
    /**
     * 租赁仓库认证 - 列表
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page' => 'integer|min:1',
            'page_size' => new PageSize,
            'keywords' => 'string',
            'status' => 'integer|min:1|max:3',
            'created_at_b' => 'date_format:Y-m-d',
            'created_at_e' => 'date_format:Y-m-d|after_or_equal:created_at_b',
        ]);

        $cert = UserCertificationRenters::with(['applicant.extra', 'checkOperatorInfo']);

        if ($request->filled('keywords')) {
            $cert->hasKeyword($request->keywords);
        }

        if ($request->filled('status')) {
            $cert->where('status', $request->status);
        }

        if ($request->filled('created_at_b')) {
            $cert->where('created_at', '>', strtotime($request->created_at_b . ' 00:00:00'));
        }

        if ($request->filled('created_at_e')) {
            $cert->where('created_at', '<', strtotime($request->created_at_e . ' 23:59:59'));
        }

        $list = $cert->latest()->paginate($request->input('page_size'));

        return formatRet(0, '', $list->toArray());
    }

    /**
     * 租赁仓库认证 - 详情
     */
    public function show($id)
    {
        if (!$cert = UserCertificationRenters::find($id)) {
            return formatRet(404, '认证申请不存在', [], 404);
        }

        $cert->load(['applicant.extra', 'checkOperatorInfo']);

        return formatRet(0, '', $cert->toArray());
    }

    /**
     * 租赁仓库认证 - 审核
     */
    public function check(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|string|min:1',
            'status' => 'required|integer|in:2,3',
            'remark' => 'string|max:255',
            'rent_limit' => 'required_if:status,2|integer|min:1',
        ]);

        if (! $cert = UserCertificationRenters::with('applicant')->find($request->id)) {
            return formatRet(404, '认证申请不存在', [], 404);
        }

        if ($cert->status != UserCertificationRenters::CHECK_STATUS_ONGOING) {
            return formatRet(500, '已审核，请勿重复操作');
        }

        $cert->status = $request->status;
        $cert->check_operator = app('auth')->id();
        $cert->check_remark = $request->input('remark');
        $cert->checked_at = $cert->freshTimestamp();

        if (! $cert->save()) {
            return formatRet(500, '失败');
        }
        $text="仓库租赁认证申请未通过，请您登陆系统重新填写申请资料";

        if ($cert->isCheckSucc()) {
            // 设置账户的可创建公有和私有仓库的最大数
            if ($cert->isCheckSucc()) {
                $extra = UserExtra::firstOrCreate(['user_id' => $cert->user_id]);
                $extra->is_certificated_renter = 1;
                $extra->rent_limit = $request->rent_limit;

                if (! $extra->save()) {
                    return formatRet(500, '设置仓库数失败');
                }
                $text="仓库租赁认证申请已通过，请您登陆系统查看";
            }
        }

        $cert=$cert->toArray();
        $toMail =$cert['applicant']['email'];
        $message = new CertificationNoticeMail($toMail,$text);
        $message->onQueue('emails');
        Mail::send($message);

        return formatRet(0);
    }
}
