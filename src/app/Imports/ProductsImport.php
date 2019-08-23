<?php

namespace App\Imports;

use App\Models\Product;
use App\Services\Service\CategoryService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;



class ProductsImport implements ToCollection, WithEvents, withHeadingRow
{
    protected  $categoryRepository;
    protected  $warehouseRepository;
    protected  $userRepository;

    public function __construct(CategoryService $category)
    {
        $this->category = $category;
    }

    public function registerEvents(): array
    {
       return [
           BeforeImport::class => function () {
               HeadingRowFormatter::extend('products', function ($value) {
                   return $this->portsAfterProduct()[$value] ?? $value;
               });
               HeadingRowFormatter::default('products');
           }
       ];
    }

    /**
     * 商品信息
     * @return array
     */
    public function portsAfterProduct()
    {
        $key_arr = [
           "规格X名称",
           "规格XSKU",
           "规格X进货价",
           "规格X参考售价",
           "规格X毛重",
        ];
        $value_arr = [
           'spec_name_X',
           'spec_code_X',
           'spec_purcharse_price_X',
           'spec_sale_price_X',
           'spec_weight_X',
        ];
        for ($i =1; $i <= 10; $i++){

           $ports = array_merge($ports?? [], array_map(function ($value) use($i) {
               return str_replace('X', $i, $value);
           }, $key_arr));


           $values = array_merge($values?? [], array_map(function ($value) use($i) {
               return str_replace('X', $i, $value);
           }, $value_arr));
        }

        $ports = collect($ports)->combine($values)->toArray();

        return array_merge([
                "商品名称"=> 'name_cn',
                "商品分类"=> 'category_name',
                "商品备注"=> 'remark',
        ], $ports);
   }

    public function collection(Collection $collection)
    {
        return $collection;
    }

}
