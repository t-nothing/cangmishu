<?php

namespace App\Http\Controllers;
use App\Http\Requests\BaseRequests;
use App\Models\Shop;
use App\Models\ShopPaymentMethod;
use App\Models\ShopSenderAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class ShopController extends Controller
{
    public function index(BaseRequests $request)
    {
        app('log')->info('店铺列表',$request->all());
        $this->validate($request,[
            'created_at_b'      => 'date_format:Y-m-d',
            'created_at_e'      => 'date_format:Y-m-d|after_or_equal:created_at_b',
            'keywords'          => 'string|max:255',
            'warehouse_id'      => 'integer',
        ]);
        $batchs =   Shop::ofWarehouse($request->input('warehouse_id'))
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

    function update(BaseRequests $request,int  $id)
    {
        $this->validate($request, [
            'warehouse_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'name_cn'                   => [
               'required','string','max:100',
                Rule::unique('shop', 'name_cn')->where(function($q) use($request){
                    $q->where('owner_id',Auth::ownerId())->where('name_cn','!=', $request->name_cn);
                })
            ],
            'name_en'                   => 'required|string|max:100',
            'logo'                      => 'somtimes|url|max:100',
            'banner_background'         => 'somtimes|url|max:100',
            'default_lang'              => 'required|string|in:zh-cn,english',
            'default_currency'          => 'required|string|in:CNY,EUR,USD',
            'email'                     => 'required|email|max:100'
        ]);

        app('db')->beginTransaction();
        try
        {

            $shop = Shop::find($id);

            if (! $shop || $shop->owner_id != Auth::id()){
                return formatRet(500,'用户不存在或无权限编辑');
            }

            $shop->name_cn = $request->name_cn;
            $shop->name_en = $request->name_en;
            $shop->logo = $request->logo;
            $shop->banner_background = $request->banner_background;
            $shop->default_lang = $request->default_lang;
            $shop->default_currency = $request->default_currency;
            $shop->email = $request->email;
            $shop->save();

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


    function store(BaseRequests $request)
    {
        $this->validate($request, [
            'warehouse_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'name_cn'                   => [
               'required','string','max:100',
                Rule::unique('shop', 'name_cn')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'name_en'                   => 'required|string|max:100',
            'logo'                      => 'somtimes|url|max:100',
            'banner_background'         => 'somtimes|url|max:100',
            'default_lang'              => 'required|string|in:zh-cn,english',
            'default_currency'          => 'required|string|in:CNY,EUR,USD',
            'email'                     => 'required|email|max:100'
        ]);

        app('db')->beginTransaction();
        try
        {
            $shop = new Shop;
            $shop->warehouse_id = $request->warehouse_id;
            $shop->name_cn = $request->name_cn;
            $shop->name_en = $request->name_en;
            $shop->logo = $request->logo;
            $shop->banner_background = $request->banner_background;
            $shop->default_lang = $request->default_lang;
            $shop->default_currency = $request->default_currency;
            $shop->email = $request->email;
            $shop->owner_id = Auth::ownerId();
            $shop->is_closed = 1;
            $shop->is_stock_show = 1;
            $shop->is_price_show = 1;
            $shop->is_allow_over_order = 1;
            $shop->domain = md5($shop->warehouse_id.time());
            $shop->save();

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

        $shop->load("senderAddress","paymentMethod");

        return formatRet(0,"成功",$shop->toArray());
    }

    /**
     * 默认发件人
     */
    public function senderShow(BaseRequests $request,$id)
    {

        $shop = Shop::find($id);

        if (! $shop || $shop->owner_id != Auth::id()){
            return formatRet(500,'店铺不存在或无权限编辑');
        }

        $shop->load("senderAddress");


        if(!is_null($shop->senderAddress))
        {
            return formatRet(0,"成功", $shop->senderAddress->toArray());
        }
        return formatRet(0,"成功", []);
    }

    /**
     * 默认发件人
     */
    public function senderUpdate(BaseRequests $request, $id)
    {
        $shop = Shop::find($id);

        if (! $shop || $shop->owner_id != Auth::id()){
            return formatRet(500,'店铺不存在或无权限编辑');
        }

        $this->validate($request, 
            [
                'fullname' => 'required|string',
                'phone'    => 'required|string',
                'country'  => 'string',
                'province' => 'required|string',
                'city'     => 'required|string',
                'district' => 'required|string',
                'address'  => 'required|string',
                'postcode' => 'string'
            ]
        );

        $senderAddress = ShopSenderAddress::updateOrCreate(
            [
                'shop_id'       =>  $shop->id,
            ],
            [
                'is_default'    =>  1,
                'country'       =>  $request->country,
                'province'      =>  $request->province,
                'city'          =>  $request->city,
                'district'      =>  $request->district,
                'address'       =>  $request->address,
                'postcode'      =>  $request->postcode,
                'fullname'      =>  $request->fullname,
                'phone'         =>  $request->phone,
            ]
        );

        return formatRet(0,"成功",$senderAddress->toArray());
    }
}