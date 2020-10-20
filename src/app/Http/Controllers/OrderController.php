<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\PickAndOutRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PDF;
use App\Exports\OrderExport;
use App\Events\OrderCancel;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function export(BaseRequests $request)
    {
        $this->validate($request, [
            'page' => 'integer|min:1',
            'page_size' => new PageSize(),
            'created_at_b' => 'date:Y-m-d',
            'created_at_e' => 'date:Y-m-d',
            'status' => 'integer',
            'keywords' => 'string',
            'delivery_date' => 'date_format:Y-m-d'
        ]);
        $order = Order::ofWarehouse(app('auth')->warehouse()->id)
            ->with(['orderItems:id,name_cn,name_en,spec_name_cn,spec_name_en,amount,relevance_code,product_stock_id,order_id,pick_num,sale_price', 'warehouse:id,name_cn', 'orderType:id,name', 'operatorUser'])
            ->whose(app('auth')->ownerId());
        if ($request->filled('created_at_b')) {
            $order->where('created_at', '>', strtotime($request->created_at_b));
        }

        if ($request->filled('created_at_e')) {
            $order->where('created_at', '<', strtotime($request->created_at_e));
        }

        if ($request->filled('status')) {
            $order->where('status', $request->status);
        }

        if ($request->filled('keywords')) {
            $order->hasKeywords($request->keywords);
        }
        $order->when($request->filled('delivery_date'), function($query) use ($request){
            return $query->whereBetween ("delivery_date",
                [strtotime($request->delivery_date),strtotime($request->delivery_date ."+1 day")*1-1]);
        });

        $orders = $order->latest()->limit(5000);

        $export = new OrderExport();
        $export->setQuery($orders);

        return app('excel')->download($export,  trans("message.orderExportCaption").date('Y-m-d').'.xlsx');
    }

    public function index(BaseRequests $request)
    {
        $this->validate($request, [
            'page'          => 'integer|min:1',
            'page_size'     => new PageSize(),
            'created_at_b'  => 'date:Y-m-d',
            'created_at_e'  => 'date:Y-m-d',
            'status'        => 'integer',
            'keywords'      => 'string',
            'with_items'    => 'boolean',
            'delivery_date' => 'date_format:Y-m-d'
        ]);
        $order = Order::ofWarehouse(app('auth')->warehouse()->id)
            ->with('orderType')
            ->whose(app('auth')->ownerId());
        if ($request->filled('created_at_b')) {
            $order->where('created_at', '>', strtotime($request->created_at_b));
        }

        if ($request->filled('created_at_e')) {
            $order->where('created_at', '<', strtotime($request->created_at_e));
        }

        if ($request->filled('status')) {
            $order->where('status', $request->status);
        }

        if ($request->filled('keywords')) {
            $order->hasKeywords($request->keywords);
        }
        $order->when($request->filled('delivery_date'), function($query) use ($request){
            return $query->whereBetween ("delivery_date",
                [strtotime($request->delivery_date),strtotime($request->delivery_date ."+1 day")*1-1]);
        });
        $order->when(
            ($request->filled('with_items') && $request->with_items),
            function($q)use($request) {
                        $q->with('orderItems:order_id,name_cn,amount,sale_price,sale_currency,spec_name_cn,pic');
                    }
        );

        $orders = $order->latest()->paginate($request->input('page_size',10));
        $result = $orders->toArray();
        foreach ($result['data'] as $key => $value) {
            
            $result['data'][$key]['track_url'] = "";
            if($value['status'] >= Order::STATUS_SENDING) {
                $result['data'][$key]['track_url'] = "https://www.kuaidi100.com/chaxun?com=".$value['express_code']."&nu=".$value['express_num'];
            }
        }
        return formatRet(0, '', $result);
    }

    public function show(BaseRequests $request, $order_id)
    {
        $order = Order::where('owner_id',Auth::ownerId())->with(['orderItems.spec:id,total_shelf_num','warehouse:id,name_cn', 'orderType:id,name', 'operatorUser'])->find($order_id);
        if(!$order){
            return formatRet("500", trans("message.orderNotExist"));
        }
        $order->append(['out_sn_barcode']);

        // $order->setHidden(['receiver_email,receiver_country','receiver_province','receiver_city','receiver_postcode','receiver_district','receiver_address','send_country','send_province','send_city','send_postcode','send_district','send_address','is_tobacco','mask_code','updated_at','line_name','line_id']);
        $order = $order->toArray();

       return formatRet(0, trans("message.success"),$order);
    }


    public function store(CreateOrderRequest $request)
    {
        app('log')->info('新增出库单',$request->all());
        app('db')->beginTransaction();
        try {

            $request->warehouse_id = app('auth')->warehouse()->id;

            $order = app('order')->setSource("自建")->create($request);
            if(!isset($order->out_sn))
            {
                throw new \Exception(trans("message.orderAddFailed"), 1);
                
            }
            app('db')->commit();
            return formatRet(0,trans("message.orderAddSuccess"), $order->toArray());
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('新增出库单失败',['msg'=>$e->getMessage()]);
            return formatRet(500, trans("message.orderAddFailed"));
        }
        
    }

//    public function destroy(BaseRequests $request,$order_id)
//    {
//        app('log')->info('取消订单',['order_id'=>$order_id,'warehouse_id' =>app('auth')->warehouse()->id]);
//        $this->validate($request,[
//            'warehouse_id' =>  [
//                'required','integer','min:1',
//                Rule::exists('warehouse')->where(function($q){
//                    $q->where('owner_id',Auth::ownerId());
//                })
//            ],
//        ]);
//
//        $order = Order::where('warehouse_id',app('auth')->warehouse()->id)->find($order_id);
//        if(!$order){
//            return formatRet(500, trans("message.orderNotExist"));
//        }
//        if ($order->owner_id != Auth::ownerId()){
//            return formatRet(500, trans("message.noPermission"));
//        }
//        try{
//            Order::where('id',$order_id)->delete();
//            return formatRet(0);
//        }catch (\Exception $e){
//            app('log')->info('取消订单失败',['msg' =>$e->getMessage()]);
//            return formatRet(500,"取消订单失败");
//        }
//    }


    public function pickAndOut(PickAndOutRequest $request)
    {

        app('log')->info('新增出库拣货单',$request->all());
        app('db')->beginTransaction();
        try {
            app("store")->pickAndOut($request->all());


            if($request->filled('express_code') && $request->filled('express_num')) {
                app('order')->updateExpress($request,$request->order_id, true);
            }

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('出库拣货失败',['msg'=>$e->getMessage()]);
            return formatRet(500, trans("message.orderPickingFailed", ["message"=>$e->getMessage()]));
        }
        
        return formatRet(0, trans("message.orderPickingSuccess"));
    }

    /**
     * 支付状态列表
     */
    public function payStatusList(){
        return formatRet(
            0,
            trans("message.success"),
            [
                ['id'=> Order::ORDER_PAY_STATUS_UNPAY , 'name'=> trans("message.orderStatusUnpay")],
                ['id'=> Order::ORDER_PAY_STATUS_REFUND , 'name'=> trans("message.orderStatusRefund")],
                ['id'=> Order::ORDER_PAY_STAUTS_PAID , 'name'=> trans("message.orderStatusPaid")],
            ]
        );
    }

    /**
     * 支付方式列表
     */
    public function payTypeList(){
        return formatRet(
            0,
            trans("message.success"),
            [
                ['id'=>  Order::ORDER_PAY_TYPE_ALIPAY, 'name'=>  trans("message.orderPaymentAlipay")],
                ['id'=>  Order::ORDER_PAY_TYPE_WECHAT, 'name'=>  trans("message.orderPaymentWechat")],
                ['id'=>  Order::ORDER_PAY_TYPE_BANK, 'name'=> trans("message.orderPaymentBank")],
                ['id'=>  Order::ORDER_PAY_TYPE_CASH, 'name'=>  trans("message.orderPaymentCash")],
                ['id'=>  Order::ORDER_PAY_TYPE_OTHER, 'name'=>  trans("message.orderPaymentOther")],
            ]
        );
    }

    /**
     * 订单状态列表
     */
    public function statusList(){
        return formatRet(
            0,
            trans("message.success"),
            [
                ['id'=>  Order::STATUS_DEFAULT, 'name'=>  trans("message.orderStatusUnConfirm")],
                // ['id'=>  Order::STATUS_PICKING, 'name'=>  '拣货中'],
                // ['id'=>  Order::STATUS_PICK_DONE, 'name'=>  '已出库'],
                ['id'=>  Order::STATUS_WAITING, 'name'=>  trans("message.orderStatusUnSend")],
                ['id'=>  Order::STATUS_SENDING, 'name'=>  trans("message.orderStatusSending")],
                ['id'=>  Order::STATUS_SUCCESS, 'name'=>  trans("message.orderStatusSuccess")],
                ['id'=>  Order::STATUS_CANCEL, 'name'=>  trans("message.orderStatusCancel")],
            ]
        );
    }

    /**
     * 取消订单
     */
    public function cancelOrder(BaseRequests $request,$order_id)
    {
        app('log')->info('request',$request->all());
        app('log')->info('取消订单',['order_id'=>$order_id,'warehouse_id' =>app('auth')->warehouse()->id]);
        // $this->validate($request,[
        //     'warehouse_id' =>  [
        //         'required','integer','min:1',
        //         Rule::exists('warehouse','id')->where(function($q){
        //             $q->where('owner_id',Auth::ownerId());
        //         })
        //     ],
        // ]);
        $order = Order::where('warehouse_id', app('auth')->warehouse()->id)->find($order_id);
        if(!$order){
            return formatRet(500, trans("message.orderNotExist"));
        }
        if ($order->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }

        $order->load("orderItems");
        $order->update(['status'=>Order::STATUS_CANCEL]);
        event(new OrderCancel($order->toArray()));
        return formatRet(0,trans("message.success"));
    }

    /**
     * 更新运单号
     */
    public function updateExpress(BaseRequests $request,$id)
    {
        $this->validate($request,[
            'express_code'           => 'required|string|string|max:255',
            'express_num'            => 'required|string|max:255',
            'shop_remark'            => 'string|max:255',
        ]);

        $order = Order::find($id);
        if(!$order){
            return formatRet(500, trans("message.orderNotExist"));
        }
        if ($order->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }
        if ($order->status < Order::STATUS_PICKING){
            return formatRet(500, trans("message.orderOpStopByUnPick"));
        }

        try {
            app('order')->updateExpress($request,$id);
            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('更新发货信息失败',['msg'=>$e->getMessage()]);
            return formatRet(500,  trans("message.failed"));
        }
        return formatRet(0,trans("message.success"));

    }

     /**
     * 更新支付价格
     */
    public function updatePayStatus(BaseRequests $request,$id)
    {
        $this->validate($request,[
            'pay_status'                => 'required|integer|min:0',
            'pay_type'                  => 'required|integer|min:1',
            'sub_pay'                   => 'required|numeric|min:0',
            'payment_account_number'    => 'string|max:100',
        ]);
        
        $order = Order::find($id);
        if(!$order){
            return formatRet(500, trans("message.orderNotExist"));
        }
        if ($order->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }

        try {
            app('order')->updatePay($request,$id);
            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('更新支付信息失败',['msg'=>$e->getMessage()]);
            return formatRet(500, trans("message.failed"));
        }
        return formatRet(0,trans("message.success"));
    }

    /**
     * 设为签收
     **/
    public function completed(BaseRequests $request,$id ){
        $order = Order::find($id);
        if(!$order){
            return formatRet(500, trans("message.orderNotExist"));
        }
        if ($order->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }
        try {
            app('order')->updateRceived($request,$id);
            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('更新支付信息失败',['msg'=>$e->getMessage()]);
            return formatRet(500, trans("message.failed"));
        }
        return formatRet(0,trans("message.success"));
    }

    public function  UpdateData(UpdateOrderRequest $request,$order_id )
    {
        app('log')->info('修改出库单数据',['order_id'=>$order_id,'warehouse_id' =>app('auth')->warehouse()->id]);
        $order = Order::where('warehouse_id',app('auth')->warehouse()->id)->find($order_id);
        if(!$order){
            return formatRet(500, trans("message.orderNotExist"));
        }
        try {
            app('order')->updateData($request,$order);
            app('db')->commit();
            return formatRet(0,trans("message.success"));
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('修改出库单数据失败',['msg'=>$e->getMessage()]);
            return formatRet(500, trans("message.failed"));
        }
    }

    /**
     * 更新为发货
     */
    public function setToSend(BaseRequests $request,$id)
    {

        $order = Order::find($id);
        if(!$order){
            return formatRet(500, trans("message.orderNotExist"));
        }
        if ($order->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }
        if ($order->status < Order::STATUS_PICKING){
            return formatRet(500, trans("message.orderOpStopByUnPick"));
        }

        try {
            app('order')->updateSend($id);
            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('更新发货信息失败',['msg'=>$e->getMessage()]);
            return formatRet(500,  trans("message.failed"));
        }
        return formatRet(0,trans("message.success"));

    }

    /**
     * 预览PDF
     **/
    public function pdf($id, $template = '')
    {

        $order = Order::find($id);
        if(!$order){
            return formatRet("500", trans("message.orderNotExist"));
        }

        if ($order->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }
        
        $order->load(['orderItems:id,name_cn,name_en,spec_name_cn,spec_name_en,amount,relevance_code,product_stock_id,order_id,pick_num,sale_price','orderItems.stocks:item_id,pick_num,warehouse_location_code,relevance_code,stock_sku', 'warehouse:id,name_cn', 'orderType:id,name', 'operatorUser']);
        $order->append(['out_sn_barcode']);

        // $order->setHidden(['receiver_email,receiver_country','receiver_province','receiver_city','receiver_postcode','receiver_district','receiver_address','send_country','send_province','send_city','send_postcode','send_district','send_address','is_tobacco','mask_code','updated_at','line_name','line_id']);

      
        $templateName = "pdfs.order.template_".strtolower($template);
        if(!in_array(strtolower($template), ['out','pick'])){
            $templateName = "pdfs.order.template_pick";
        }


        return view($templateName, [
            'order' => $order->toArray(),
        ]);
    }

    /**
     * 查看公开信息
     **/
    public function shareView(BaseRequests $request)
    {

        $this->validate($request,[
            'id'            => 'required|integer|min:0',
            'share_code'    => 'required|string|max:100|min:1',
            'type'          => 'required|string|in:part,detail',
            'mobile'        => 'required_if:type,detail|string',
        ]);
        app('log')->info('查看公开信息',$request->all());


        $order = Order::find($request->id);
        if(!$order){
            return formatRet(500, trans("message.orderNotExist"));
        }
        if ($order->share_code != $request->share_code && trim($request->share_code)!=""){
            return formatRet(500, trans("message.noPermission"));
        }

        if($request->type == "part") {
            $result["out_sn"]               = $order->out_sn;
            $result["source"]               = $order->source;
            $result["receiver_fullname"]    = $order->receiver_fullname;
            $result["status"]               = $order->status;
            $result["status_id"]            = $order->status_id;

            return formatRet(0, trans("message.success"),$result);
        }
        if($order->receiver_phone != $request->mobile) {
            return formatRet(500, trans("message.noPermission"));
        }


        $order = $order->load(['orderItems.spec:id,total_shelf_num']);

        $order = $order->toArray();

        return formatRet(0,trans("message.success"), $order->toArray());
    }

    /**
     * 设置为公开信息
     **/
    public function shareOrder(BaseRequests $request,$id)
    {

        $order = Order::find($request->id);
        if(!$order){
            return formatRet(500, trans("message.orderNotExist"));
        }
        if ($order->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }

        try {
            $shareCode = app('order')->updateToShare($id);
            return formatRet(0,trans("message.success"), ["share_code"=>$shareCode]);
        } catch (\Exception $e) {
            return formatRet(500,  trans("message.failed"));
        }
        
    }

    /**
     * 公开信息下载PDF
     **/
    public function shareDownload(BaseRequests $request)
    {

        $this->validate($request,[
            'id'            => 'required|integer|min:0',
            'share_code'    => 'required|string|max:100|min:1',
            'mobile'        => 'required|string',
        ]);
        app('log')->info('公开信息下载PDF',$request->all());

        $id =  $request->id;

        $order = Order::find($id);
        if(!$order){
            return formatRet(500, trans("message.orderNotExist"));
        }
        

        if ($order->share_code != $code && trim($code)!=""){
            return formatRet(500, trans("message.noPermission"));
        }

        if($order->receiver_phone != $request->mobile) {
            return formatRet(500, trans("message.noPermission"));
        }

        $order->load(['orderItems:id,name_cn,name_en,spec_name_cn,spec_name_en,amount,relevance_code,product_stock_id,order_id,pick_num,sale_price','orderItems.stocks:item_id,pick_num,warehouse_location_code,relevance_code,stock_sku', 'warehouse:id,name_cn', 'orderType:id,name', 'operatorUser']);
        $order->append(['out_sn_barcode']);

        $template = "out";
        $templateName = "pdfs.order.template_".strtolower($template);
        if(!in_array(strtolower($template), ['out','pick'])){
            $templateName = "pdfs.order.template_pick";
        }

        $pdf = PDF::setPaper('a4');

        // $file = $order->out_sn . "_{$templateName}.pdf";
        $fileName = sprintf("%s_%s_%s.pdf", $order->out_sn, template_download_name($templateName, "en"), md5($order->out_sn.$order->created_at));
        
        $filePath = sprintf("%s/%s", storage_path('app/public/pdfs/'), $fileName);
        if(!file_exists($filePath)) {

            $pdf->loadView($templateName, ['order' => $order->toArray()])->save($filePath);
        }

        if($request->filled("require_url") && $request->require_url == 1) {

            $url = asset('storage/pdfs/'.$fileName);
            return formatRet(0,trans("message.success"), ["url"=>$url]);
        }

        return response()->download($filePath, $fileName);
    }

    /**
     * 下载PDF
     *
    */
    public function download(BaseRequests $request, $id, $template = '')
    {
        $order = Order::find($id);
        if(!$order){
            return formatRet("500", trans("message.orderNotExist"));
        }

        if ($order->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }

        $order->load(['orderItems:id,name_cn,name_en,spec_name_cn,spec_name_en,amount,relevance_code,product_stock_id,order_id,pick_num,sale_price','orderItems.stocks:item_id,pick_num,warehouse_location_code,relevance_code,stock_sku', 'warehouse:id,name_cn', 'orderType:id,name', 'operatorUser']);
        $order->append(['out_sn_barcode']);

        // $order->setHidden(['receiver_email,receiver_country','receiver_province','receiver_city','receiver_postcode','receiver_district','receiver_address','send_country','send_province','send_city','send_postcode','send_district','send_address','is_tobacco','mask_code','updated_at','line_name','line_id']);

      
        $templateName = "pdfs.order.template_".strtolower($template);
        if(!in_array(strtolower($template), ['out','pick'])){
            $templateName = "pdfs.order.template_pick";
        }

        $pdf = PDF::setPaper('a4');

        // $file = $order->out_sn . "_{$templateName}.pdf";
        $fileName = sprintf("%s_%s_%s.pdf", $order->out_sn, template_download_name($templateName, "en"), md5($order->out_sn.$order->created_at));
        
        $filePath = sprintf("%s/%s", storage_path('app/public/pdfs/'), $fileName);
        if(!file_exists($filePath)) {

            $pdf->loadView($templateName, ['order' => $order->toArray()])->save($filePath);
        }

        if($request->filled("require_url") && $request->require_url == 1) {

            $url = asset('storage/pdfs/'.$fileName);
            return formatRet(0,trans("message.success"), ["url"=>$url]);
        }

        return response()->download($filePath, $fileName);
        // return $pdf->loadView($templateName, ['order' => $order->toArray()])->download($file);

    }
}
