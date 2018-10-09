<?php

namespace App\Http\Controllers\Pc;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductStock;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->warehouse = app('auth')->warehouse();
    }

    public function ean(Request $request)
    {
        $this->validate($request, [
            'ean' => 'required|string',
        ]);

        if (! $stock = ProductStock::has('spec.product')->ofWarehouse($this->warehouse->id)->where('ean', $request->ean)->first()) {
            return formatRet(500, '找不到ean');
        }

        $stock->load('spec.product');

        $data = [
            'ean' => $stock->ean,
            'product' => $stock->spec->product->only(['name_cn', 'name_en']),
            'spec' => $stock->spec->only(['name_cn', 'name_en']),
            // 'name_cn' => $stock->spec->product_name_cn,
            // 'name_en' => $stock->spec->product_name_en,
        ];

        return formatRet(0, '', $data);
    }
}
