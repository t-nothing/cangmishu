<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * 订单导出
 **/
class OrderExport implements FromQuery, WithMapping, WithHeadings, WithStrictNullComparison, ShouldAutoSize
{
    protected $query;

    public function setQuery($query){
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    /**
     * @var ProductStock $order
     */
    public function map($order): array
    {
        // app('log')->info('map order export', [$order->toArray()]);
        $arr = [
            $order->out_sn,
            $order->castsTo('created_at'),
            $order->express_num,
            $order->source,
            $order->sub_order_qty,
            $order->receiver_fullname,
            $order->receiver_phone,
            $order->receiver_province,
            $order->receiver_city,
            $order->receiver_district,
            $order->receiver_address,
            $order->receiver_postcode,
        ];

        $newOrders = $order->toArray();
        foreach ($newOrders['order_items'] as $key => $value) {
            $arr[] = $value["name_cn"]." - ". $value["spec_name_cn"];
            $arr[] = $value["relevance_code"];
            $arr[] = $value["amount"];
            $arr[] = $value["sale_price"];
        }

        return $arr;
    }

    public function headings(): array
    {
        return [
            '出库单号',
            '下单日期',
            '运单号',
            '订单来源',
            '下单数量',
            '收件人姓名',
            '收件人电话',
            '收件人省',
            '收件人市',
            '收件人区',
            '收件人邮编',
            '收件人详细地址',
            
            '订单明细1名称',
            '订单明细1SKU',
            '订单明细1数量',
            '订单明细1价格',

            '订单明细2名称',
            '订单明细2SKU',
            '订单明细2数量',
            '订单明细2价格',

            '订单明细3名称',
            '订单明细3SKU',
            '订单明细3数量',
            '订单明细3价格',

            '订单明细4名称',
            '订单明细4SKU',
            '订单明细4数量',
            '订单明细4价格',

            '订单明细5名称',
            '订单明细5SKU',
            '订单明细5数量',
            '订单明细5价格',

            '订单明细6名称',
            '订单明细6SKU',
            '订单明细6数量',
            '订单明细6价格',

            '订单明细7名称',
            '订单明细7SKU',
            '订单明细7数量',
            '订单明细7价格',

            '订单明细8名称',
            '订单明细8SKU',
            '订单明细8数量',
            '订单明细8价格',

            '订单明细9名称',
            '订单明细9SKU',
            '订单明细9数量',
            '订单明细9价格',

            '订单明细10名称',
            '订单明细10SKU',
            '订单明细10数量',
            '订单明细10价格',
        ];
    }
}
