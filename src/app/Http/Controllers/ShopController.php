<?php

namespace App\Http\Controllers;
use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Models\Shop;
use App\Models\ShopPaymentMethod;
use App\Models\ShopSenderAddress;
use App\Models\ShopProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;


class ShopController extends Controller
{
    /**
     * 店铺列表
     */
    public function index(BaseRequests $request)
    {
        app('log')->info('店铺列表',$request->all());
        $this->validate($request,[
            'created_at_b'      => 'date_format:Y-m-d',
            'created_at_e'      => 'date_format:Y-m-d|after_or_equal:created_at_b',
            'keywords'          => 'string|max:255',
            'warehouse_id'      => 'required|integer',
        ]);
        $batchs =   Shop::ofWarehouse($request->input('warehouse_id'))
            ->with('senderAddress')
            ->where('owner_id',Auth::ownerId())
            ->when($request->filled('created_at_b'),function ($q) use ($request){
                return $q->where('created_at', '>', strtotime($request->input('created_at_b')));
            })
            ->when($request->filled('created_at_e'),function ($q) use ($request){
                return $q->where('created_at', '<', strtotime($request->input('created_at_e')));
            })
            ->when($request->filled('keywords'),function ($q) use ($request){
                return $q->hasKeyword($request->input('keywords'));
            })
            ->latest()->paginate($request->input('page_size',10));

            $re = $batchs->toArray();

//
            $data = collect($re['data'])->map(function($v){
                unset($v['batch_products']);
                return $v;
            })->toArray();
            $re['data'] = $data;
        return formatRet(0,'',$re);
    }

    /**
     * 店铺修改
     */
    function update(UpdateShopRequest $request,int  $id)
    {
        $data = $request->all();
        app('db')->beginTransaction();
        try
        {

            $shop = Shop::find($id);
            $shop->name_cn              = $data['name_cn'];
            $shop->name_en              = $data['name_en']??$data['name_cn'];
            $shop->logo                 = $data['logo']??'';
            $shop->banner_background    = $data['banner_background']??'';
            $shop->default_lang         = $data['default_lang']??'cn';
            $shop->default_currency     = $data['default_currency']??'CNY';
            $shop->email                = $data['email']??'';
            $shop->owner_id             = Auth::ownerId();
            $shop->remark_cn            = $data['remark']??'';
            $shop->is_closed            = 0;
            $shop->is_stock_show        = 1;
            $shop->is_price_show        = 1;
            $shop->is_allow_over_order  = 1;
            $shop->domain               = md5($data['name_cn']);
            
            $shop->save();

            // $items = [];

            // foreach ($data['items'] as $key => $value) {
            //     $items[] = new ShopProduct([
            //         'product_id'        =>  $value['product_id'],
            //         'sale_price'        =>  $value['sale_price'],
            //         'is_shelf'          =>  $value['is_shelf'],
            //         'remark'            =>  $value['remark'],
            //         'pics'              =>  json_encode($value['pics'], true),
            //     ]);
            // }

            // ShopProduct::where('shop_id', $shop->id)->delete();
            ShopSenderAddress::where('shop_id', $shop->id)->delete();

            // $shop->items()->saveMany($items);
            $contact = $data['contact'];

            $shop->senderAddress()->save(new ShopSenderAddress([
                'country'           =>  $contact['country'],
                'province'          =>  $contact['province'],
                'city'              =>  $contact['city'],
                'district'          =>  $contact['district'],
                'address'           =>  $contact['address'],
                'fullname'          =>  $contact['fullname'],
                'phone'             =>  $contact['phone'],
            ]));


            app('db')->commit();
        }
        catch (\Exception $e)
        {
            app('db')->rollback();
            app('log')->error('修改店铺失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"修改店铺失败");
        }

        return formatRet(0);
    }

    /**
     * 店铺新增
     */
    function store(CreateShopRequest $request)
    {

        $data = $request->all();
        app('db')->beginTransaction();
        try
        {

            $shop = new Shop;
            $shop->warehouse_id         = $data['warehouse_id'];
            $shop->name_cn              = $data['name_cn'];
            $shop->name_en              = $data['name_en']??$data['name_cn'];
            $shop->logo                 = $data['logo']??'';
            $shop->banner_background    = $data['banner_background']??'';
            $shop->default_lang         = $data['default_lang']??'cn';
            $shop->default_currency     = $data['default_currency']??'CNY';
            $shop->email                = $data['email']??'';
            $shop->owner_id             = Auth::ownerId();
            $shop->remark_cn            = $data['remark']??'';
            $shop->is_closed            = 0;
            $shop->is_stock_show        = 1;
            $shop->is_price_show        = 1;
            $shop->is_allow_over_order  = 1;
            $shop->save();
            $shop->domain               = md5($shop->id);
            $shop->weapp_qrcode         = sprintf("%s.png", $shop->domain);
            $shop->sort_num             = 0;
            
            $app = app('wechat.mini_program');
            $response = $app->app_code->get('/pages/index/index?shop='.$shop->id);

            if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {

                $filePath = storage_path('/app/public/weapp/') ;
                $filename = $response->saveAs($filePath, sprintf("%s.png", $shop->domain));

                $url = Storage::url('weapp/'.$filename);
                $shop->weapp_qrcode         = app('url')->to($url) ;
                
            }

            $shop->save();

            // $items = [];

            // foreach ($data['items'] as $key => $value) {
            //     $items[] = new ShopProduct([
            //         'product_id'        =>  $value['product_id'],
            //         'sale_price'        =>  $value['sale_price'],
            //         'is_shelf'          =>  $value['is_shelf'],
            //         'remark'            =>  $value['remark'],
            //         'pics'              =>  json_encode($value['pics'], true),
            //     ]);
            // }


            // $shop->items()->saveMany($items);


            $contact = $data['contact'];

            $shop->senderAddress()->save(new ShopSenderAddress([
                'country'           =>  $contact['country'],
                'province'          =>  $contact['province'],
                'city'              =>  $contact['city'],
                'district'          =>  $contact['district'],
                'address'           =>  $contact['address'],
                'fullname'          =>  $contact['fullname'],
                'phone'             =>  $contact['phone'],
            ]));


            app('db')->commit();
        }
        catch (\Exception $e)
        {
            app('db')->rollback();
            app('log')->error('新增店铺失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"新增店铺失败");
        }

        return formatRet(0);
    }

    /**
     * 删除店铺
     */
    public function destroy(BaseRequests $request,$id)
    {
        $shop = Shop::find($id);

        if (! $shop || $shop->owner_id != Auth::id()){
            return formatRet(500,'店铺不存在或无权限编辑');
        }

        app('db')->beginTransaction();
        try {
            $shop->delete();
            ShopProduct::where('shop_id', $shop->id)->delete();
            ShopSenderAddress::where('shop_id', $shop->id)->delete();
            app('db')->commit();

        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500,'删除店铺失败');
        }
        return formatRet(0);
    }

    /**
     * 店铺详细
     */
    public function show(BaseRequests $request,$id)
    {
        $shop = Shop::find($id);

        if (! $shop || $shop->owner_id != Auth::id()){
            return formatRet(500,'店铺不存在或无权限编辑');
        }

        $shop->load("senderAddress");

        return formatRet(0,"成功",$shop->toArray());
    }
}