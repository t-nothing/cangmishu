<?php
/**
 * 快递公司.
 */

namespace App\Http\Controllers\Open;

use App\Http\Controllers\Controller;

class ExpressController extends Controller
{

    public function list(){

        return formatRet(0, '', app('ship')->expressCompanyList());
      
    }
}