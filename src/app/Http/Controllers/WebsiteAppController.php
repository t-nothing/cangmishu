<?php
/**
 * @Author: h9471
 * @Created: 2020/11/2 16:28
 */

namespace App\Http\Controllers;

class WebsiteAppController extends Controller
{
    public function info()
    {
        return formatRet(0, __('message.success'), [
            'app_id' => 'wxd2ef11377217653d',
        ]);
    }
}
