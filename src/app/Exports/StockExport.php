<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StockExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize
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
     * @var Invoice $spec
     */
    public function map($spec): array
    {
    	return [
            $spec->product_name_cn,
            $spec->product_name_en,
            $spec->relevance_code,
            $spec->owner?$spec->owner->nickname:"",
            $spec->stock_in_warehouse,
            $spec->stock_to_be_on_shelf,
            $spec->stock_on_shelf,
            $spec->reserved_num,
            $spec->available_num,
            $spec->stock_entrance_times,
            $spec->stock_entrance_qty,
            $spec->stock_out_times,
            $spec->stock_out_qty,
            $spec->stocks_count,
//            $spec->product->category->feature->name_cn,
        ];
    }

    public function headings(): array
    {
        return [
            '货品中文名称',
			'货品英文名称',
			'外部编码',
			'所属商家',
			'总仓库库存',
			'未上架库存',
			'上架库存',
			'锁定库存',
			'可用库存',
			'入库次数',
			'入库数量',
			'出库次数',
			'出库数量',
			'SKU数量',
//			'特性',
        ];
    }
}
