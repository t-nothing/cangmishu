<?php

namespace App\Http\Controllers\Open;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OpenController extends Controller
{
    public function ping(Request $request)
    {
        return formatRet(0, '成功');
    }
}
