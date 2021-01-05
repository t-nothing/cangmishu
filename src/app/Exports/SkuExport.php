<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SkuExport implements FromQuery, WithMapping, WithHeadings, WithStrictNullComparison, ShouldAutoSize
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
     * @var ProductStock $stock
     */
    public function map($stock): array
    {
        // print_r($stock->locations->toArray());exit;
        return [
            $stock->sku,
            $stock->spec->product_name_cn,
            // $stock->spec->product_name_en,
            $stock->relevance_code,
            $stock->stock_num,
            $stock->ean,
            $stock->production_batch_number,
            $stock->castsTo('expiration_date'),
            $stock->castsTo('best_before_date'),
            $stock->recount_times,
            // $stock->locations->pluck('warehouse_location_code')->implode(','),
        ];
    }

    public function headings(): array
    {
        return [
            '入库批次号',
            '货品中文名称',
            // '货品英文名称',
            'SKU',
            '仓库库存',
            'EAN',
            '出产批次号',
            '保质期',
            '最佳食用期',
            '盘点次数',
            // '位置',
        ];
    }
}
