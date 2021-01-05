<?php
/**
 * 店铺地址
 */

namespace App\Http\Controllers\Open\Shop;
use App\Http\Requests\BaseRequests;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserAddressRequest;
use App\Models\ShopUserAddress;
use App\Rules\PageSize;

class UserAddressController extends Controller
{
    /**
     * 地址详情
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        $address = ShopUserAddress::query()
            ->where('shop_user_id', auth('shop')->id())
            ->findOrFail($id);

        return formatRet(0, '', $address);
    }

    /**
     * 地址更新
     *
     * @param  int  $id
     * @param  UserAddressRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(int $id, UserAddressRequest $request)
    {
        $data = $request->validated();

        $address = ShopUserAddress::query()
            ->where('shop_user_id', auth('shop')->id())
            ->findOrFail($id);

        $address->update($data);

        return formatRet(0, '操作成功', []);
    }

    /**
     * 地址更新
     *
     * @param  UserAddressRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserAddressRequest $request)
    {
        $data = $request->validated();

        $data['shop_user_id'] = auth('shop')->id();

        $address = new ShopUserAddress($data);

        if ($address->save()) {
            return formatRet(0, '', []);
        }

        return formatRet(500, '新建失败', []);
    }

    /**
     * 推荐店铺列表
     *
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(BaseRequests $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize(),
        ]);

        $list = ShopUserAddress::query()
            ->where('shop_user_id', auth('shop')->id())
            ->orderBy('is_default', 'desc')
            ->orderBy('id','desc')
            ->paginate($request->input('page_size',10));

        return formatRet(0, '', $list);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(int $id)
    {
        $address = ShopUserAddress::query()
            ->where('shop_user_id', auth('shop')->id())
            ->findOrFail($id);

        if ($address->delete()) {
            return formatRet(0, '删除成功', []);
        }

        return formatRet(500, '删除失败', []);
    }
}
