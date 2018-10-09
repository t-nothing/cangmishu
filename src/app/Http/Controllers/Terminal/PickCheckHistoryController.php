<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Pick;
use App\Models\PickCheckHistory;
use Illuminate\Support\Carbon;

class PickCheckHistoryController extends Controller
{
    public function __construct()
    {
        $this->warehouse = app('auth')->warehouse();
        $this->user = app('auth')->user();
    }

    /**
     * 复核
     */
    public function create(Request $request)
    {
        app('log')->info('复核', $request->input());

        $this->validate($request, [
            'line_name'     => 'required|string|max:255',
            'shipment_nums' => 'required|string',
            'photo'         => 'required|string',
        ]);

        $shipment_nums = explode(',', $request->shipment_nums);

        $now = Carbon::now();

        $data = [];

        if (! $shipment_nums) {
            return formatRet(500, '单号不能为空');
        }

        foreach ($shipment_nums as $key => $shipment_num) {
            $pick = Pick::ofWarehouse($this->warehouse->id)
                ->where('shipment_num', $shipment_num)
                ->first();

            if (! $pick) {
                return formatRet(500, '单号'.$shipment_num.'无效');
            }

            $data[] = [
                'shipment_num' => $shipment_num,
                'line_name' => $request->line_name,
                'photo' => $request->photo,
                'operator_id' => $this->user->id,
                'date' => $now,
            ];
        }

        app('db')->beginTransaction();
        try {

            foreach ($data as $d) {
                PickCheckHistory::create($d);
            }

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();

            app('log')->info('复核', ['msg' => $e->getMessage()]);

            return formatRet(500, '保存数据失败');
        }

        return formatRet(0, '');
    }
}
