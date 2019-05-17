<?php

namespace App\Imports;

use App\Models\ProductSpec;
use App\Repositories\ProductRepository;
use App\Services\Service\ProductService;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;


class ProductSpecImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
{
    protected  $productRepository;

    public function __construct(ProductService $product)
    {
        $this->product = $product;
    }

    public function model(array $row)
    {
        $product_id = $this->product->getProductIdByNameCn($row['name_cn']);
        return new ProductSpec([
            'warehouse_id' => app('auth')->warehouse()->id,
            'name_cn' => $row['specification_name_cn'],
            'name_en' => $row['specification_name_en'],
            'relevance_code' =>$row['relevance_code'],
            'owner_id' => app('auth')->ownerId(),
            'product_id' => $product_id
        ]);
    }


    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            'relevance_code' => Rule::unique('product_spec')->where(function ($query) {
                $query->where('warehouse_id',app('auth')->warehouse()->id)
                      ->where('owner_id',app('auth')->ownerId());
            }),
            'specification_name_cn' => 'required|string|max:255',
            'specification_name_en' => 'required|string|max:255',
            'name_cn' => Rule::exists('product')->where(function ($query) {
                $query->where('warehouse_id',app('auth')->warehouse()->id)
                      ->where('owner_id',app('auth')->ownerId());
            })
        ];
    }

}
