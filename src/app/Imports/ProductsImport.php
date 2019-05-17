<?php

namespace App\Imports;

use App\Models\Product;
use App\Services\Service\CategoryService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class ProductsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
{
    protected  $categoryRepository;
    protected  $warehouseRepository;
    protected  $userRepository;

    public function __construct(CategoryService $category)
    {
        $this->category = $category;
    }

    public function model(array $row)
    {
        $category_id = $this->category->getCategoryIdByNameCn($row['category']);
        return new Product([
            'warehouse_id' =>app('auth')->warehouse()->id,
            'name_cn' => $row['name_cn'],
            'name_en' => $row['name_en'],
            'category_id' => $category_id,
            'owner_id' =>app('auth')->ownerId(),
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
            'category' => 'required|exists:category,name_cn',
            'name_cn' => Rule::unique('product')->where(function ($query) {
                $query->where('warehouse_id',app('auth')->warehouse()->id)
                      ->where('owner_id',app('auth')->ownerId());
            }),
            'name_en' => 'required|string|max:255',
        ];
    }



}
