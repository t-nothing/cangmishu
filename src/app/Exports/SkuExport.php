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
        return [
            $stock->sku,
            $stock->spec->product_name_cn,
            $stock->spec->product_name_en,
            $stock->relevance_code,
            $stock->stockin_num,
            $stock->ean,
            $stock->production_batch_number,
            $stock->castsTo('expiration_date'),
            $stock->castsTo('best_before_date'),
            $stock->location->code ?? '',
            $stock->edit_count,
        ];
    }

    public function headings(): array
    {
        return [
            '入库批次号',
            '货品中文名称',
            '货品英文名称',
            'SKU',
            'EAN',
            '出产批次号',
            '保质期',
            'BBD',
            '位置',
            '盘点次数',
        ];
    }
}
