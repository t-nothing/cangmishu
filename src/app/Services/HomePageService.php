<?php

namespace App\Services;

use App\Models\HomePageAnalyze;
use App\Models\HomePageNotice;
use App\Models\LeaseApplicationInfo;
use App\Models\UserCertificationOwner;
use App\Models\UserCertificationRenters;
use App\Models\Warehouse;
use App\Models\User;

class HomePageService
{
    //首页通知保存数据
    public function homePageNoticeStore()
    {

        $userInfo = User::newModelInstance()->get();

        foreach ($userInfo as $k => $v) {
            $this->ownerCer($v['id']);
            $this->renterCer($v['id']);
            $this->applicationRenter($v['id']);
            $this->appCheckInfo($v['id']);
        }
    }

//    //首页仓库数据保存
//    public function homePageAnalyzeStore()
//    {
//        $warehouse_infos = Warehouse::with('batch')->with('order.orderItems')->with('productStock')->get()->toArray();
////        return $warehouse_infos;
//        $warehouseData = [];
//        foreach ($warehouse_infos as $key => $val) {
//            $warehouseData[$key]['warehouse_id'] = $val['id'];
//            $warehouseData[$key]['warehouse_name'] = $val['name_cn'];
//            $warehouseData[$key]['batch_count'] = 0;
//            $warehouseData[$key]['batch_product_num'] = 0;
//            //入库单数据和入库商品数量
//            foreach ($val['batch'] as $k => $v) {
//                $warehouseData[$key]['batch_product_num'] += $v['total_num']['total_stockin_num'];
//                $warehouseData[$key]['batch_count']++;
//            }
//            $warehouseData[$key]['order_count'] = 0;
//            $warehouseData[$key]['order_product_num'] = 0;
//            //出库单数据和出库商品数量
//            foreach ($val['order'] as $o_k => $o_v) {
//                //出库单不为空
//                if ($o_v['order_items']) {
//                    foreach ($o_v['order_items'] as $order_k => $order_v) {
//                        $warehouseData[$key]['order_product_num'] += $order_v['amount'];
//                        $warehouseData[$key]['order_count']++;
//                    }
//                }
//            }
//
//            $warehouseData[$key]['product_total'] = 0;
//            foreach ($val['product_stock'] as $stock_k => $stock_v) {
//                $warehouseData[$key]['product_total'] += $stock_v['stockin_num'];
//            }
//        }
//        $this->warehouse($warehouseData);
//    }
//
//    protected function warehouse($inData)
//    {
//        foreach ($inData as $k => $v) {
//            $analyze = HomePageAnalyze::updateOrCreate(['warehouse_id' => $v['warehouse_id']],['record_time'=>strtotime(date('Y-m-d'))]);
//
//            $analyze->warehouse_id = $v['warehouse_id'];
//            $analyze->warehouse_name = $v['warehouse_name'];
//            $analyze->batch_count = $v['batch_count'];
//            $analyze->order_count = $v['order_count'];
//            $analyze->batch_product_num = $v['batch_product_num'];
//            $analyze->order_product_num = $v['order_product_num'];
//            $analyze->product_total = $v['product_total'];
//            $analyze->record_time = strtotime(date('Y-m-d'));
//
//            $analyze->save();
//
//        }
//
//    }

    //仓库产权认证
    protected function ownerCer($user_id)
    {
        $ownerCertification = UserCertificationOwner::whose($user_id)->first();
        if ($ownerCertification) {
            $notice = HomePageNotice::where('owner_id', $user_id)->firstOrCreate(['notice_type' => UserCertificationOwner::HOME_PAGE_TYPE]);

            $notice->notice_time = strtotime(date("y-m-d"));
            $notice->notice_type = UserCertificationOwner::HOME_PAGE_TYPE;
            $notice->notice_warehouse = '';//认证不需要仓库
            $notice->owner_id = $user_id;
            $notice->notice_relation_id = $ownerCertification['id'];
            $notice->notice_status = $ownerCertification['status'];
            $notice->status = HomePageNotice::DISPLAY;
            $notice->notice_info = $this->noticeType(UserCertificationOwner::HOME_PAGE_TYPE,$ownerCertification['status']);

            $notice->save();
        }
    }

    //仓库租赁认证
    protected function renterCer($user_id)
    {
        $renterCertification = UserCertificationRenters::whose($user_id)->first();
        if ($renterCertification) {
            $notice = HomePageNotice::firstOrCreate(['notice_type' => UserCertificationRenters::HOME_PAGE_TYPE]);

            $notice->notice_time = strtotime(date("y-m-d"));
            $notice->notice_type = UserCertificationRenters::HOME_PAGE_TYPE;
            $notice->notice_warehouse = '';//认证不需要仓库
            $notice->owner_id = $renterCertification['user_id'];
            $notice->notice_relation_id = $renterCertification['id'];
            $notice->notice_status = $renterCertification['status'];
            $notice->status = HomePageNotice::DISPLAY;
            $notice->notice_info = $this->noticeType(UserCertificationRenters::HOME_PAGE_TYPE,$notice->notice_status);

            $notice->save();
        }

    }

    //申请租赁（作为租赁方）
    protected function applicationRenter($user_id)
    {
        $infos = LeaseApplicationInfo::where('owner_id', $user_id)->get();//->get('id')

        if ($infos) {

            //查询是否存在id
            foreach ($infos->toArray() as $k => $v) {
                $notice = HomePageNotice::updateOrCreate(['owner_id'=>$user_id],['notice_relation_id'=>$v['id']]);

                $notice->notice_time = strtotime(date("y-m-d"));
                $notice->notice_type = LeaseApplicationInfo::HOME_PAGE_TYPE;
                $notice->notice_warehouse = $v['warehouse_id'];
                $notice->owner_id = $user_id;
                $notice->notice_relation_id = $v['id'];
                $notice->notice_status = $v['lease_status'];
                $notice->status = HomePageNotice::DISPLAY;
                $notice->notice_info = $v['warehouse_name'] . $this->noticeType(LeaseApplicationInfo::HOME_PAGE_REN_TYPE,$v['lease_status']);
                $notice->save();
            }
        }

    }

    //审核租赁列表
    protected function appCheckInfo($user_id)
    {

        $own_warehouses = Warehouse::whose($user_id)
            ->where('type', Warehouse::TYPE_OPEN)
            ->get(['id']);

        $warehouse_ids = $own_warehouses
            ? array_column($own_warehouses->toArray(), 'id')
            : [];


        $leaseApplicationInfo = LeaseApplicationInfo::latest()->whereIn('warehouse_id', $warehouse_ids)->get();

        if ($leaseApplicationInfo) {
            foreach ($leaseApplicationInfo->toArray() as $k => $v) {
                $notice = HomePageNotice::updateOrCreate(
                    [
                        'owner_id'=>$user_id,
                        'notice_relation_id'=>$v['id']
                    ]);

                $notice->notice_time = strtotime(date("y-m-d"));
                $notice->notice_type = LeaseApplicationInfo::HOME_PAGE_TYPE;
                $notice->notice_warehouse = $v['warehouse_id'];
                $notice->owner_id = $user_id;
                $notice->notice_relation_id = $v['id'];
                $notice->notice_status = $v['lease_status'];
                $notice->status = HomePageNotice::DISPLAY;
                $notice->notice_info = $v['warehouse_name'] . $this->noticeType(LeaseApplicationInfo::HOME_PAGE_OWN_TYPE,$v['lease_status']) ;
                $notice->save();

            }
        }
    }

    private function noticeType($type,$status)
    {

        $typeArr = [
            'user_certification_owner' => '用户仓库认证状态：',
            'user_certification_renters' => '用户租赁认证状态：',
            'lease_application_info_own' => '租赁仓库审核状态：',
            'lease_application_info_ren' => '申请租赁仓库状态：'
        ];
        //认证状态 1 待审核 2 通过 3 驳回
        $statusArr = [
            1 => '待审核',
            2 => '通过',
            3 => '驳回',
        ];
        return $typeArr[$type].$statusArr[$status];
    }


}
