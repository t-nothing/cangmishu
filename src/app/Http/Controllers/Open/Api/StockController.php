<?php
/**
 * 快递公司.
 */

namespace App\Http\Controllers\Open\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{

    public function show(){


        return formatRet(0, '', Auth::warehouse()->toArray());
      
    }
}