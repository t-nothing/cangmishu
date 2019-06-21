<?php

namespace App\Models;

class HomePageAnalyze extends Model
{
    protected $table = 'home_page_analyze';

    protected $fillable = [
        'warehouse_id', 'warehouse_name','order_count','batch_count','product_total'
    ];
}