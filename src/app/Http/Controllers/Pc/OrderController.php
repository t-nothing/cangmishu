<?php

namespace App\Http\Controllers\Pc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Rules\PageSize;

class OrderController extends Controller
{
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'            => 'integer|min:1',
            'perPage'         => new PageSize,
            'warehouse_id'    => 'required|integer|min:1',
            'shipment_num'    => 'string|max:50',
            'express_num'     => 'string|max:50',
            'keyword'         => 'string|max:255',
            'postcode'        => 'string|max:20',
            // 'order_id'        => 'integer',
            'start_time'      => 'date_format:Y-m-d H:i:s|before:tomorrow',
            'over_time'       => 'date_format:Y-m-d H:i:s|after:start_time',
            'status'          => 'string|max:20',
            'is_night'        => 'boolean',
            'is_weekend'      => 'boolean',
            'deliveryOrderBy' => 'string|in:asc,desc',
            'createdOrderBy'  => 'string|in:asc,desc',
            'postcodeOrderBy' => 'string|in:asc,desc',
        ]);

        $user = Auth::user();

        $order = Order::with('orderItems')
                      ->whose($user->id)
                      ->ofWarehouse($request->warehouse_id)
                      ->where('status', '!=', Order::STATUS_CANCEL);

        $request->filled('shipment_num') AND
            $order->where('shipment_num', $request->shipment_num);

        $request->filled('express_num') AND
            $order->where('express_num', $request->express_num);

        $request->filled('keyword') AND
            $order->hasKeywords($request->keyword);

        $request->filled('postcode') AND
            $order->where('receiver_postcode', $request->postcode);

        $request->filled('start_time') AND
            $order->where('delivery_date', '>=', strtotime($request->start_time));

        $request->filled('over_time') AND
            $order->where('delivery_date', '<=', strtotime($request->over_time));

        $request->filled('status') AND
            $order->whereIn('status', explode(',', $request->status));

        $request->filled('is_night') AND
            $order->where('is_night', $request->is_night);

        $request->filled('is_weekend') AND
            $order->where('is_weekend', $request->is_weekend);

        $request->filled('deliveryOrderBy') AND
            $order->orderBy('delivery_date', $request->deliveryOrderBy);

        $request->filled('createdOrderBy') AND
            $order->orderBy('created_at', $request->createdOrderBy);

        $request->filled('postcodeOrderBy') AND
            $order->orderBy('receiver_postcode', $request->postcodeOrderBy);

        $orders = $order->paginate($request->input('perPage'));

        return formatRet(0, '', $orders->toArray());
    }
}
