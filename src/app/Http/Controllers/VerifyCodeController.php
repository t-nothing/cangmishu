<?php
/**
 * Created by PhpStorm.
 * User: NLE-Tech
 * Date: 2019/6/19
 * Time: 9:25
 */

namespace App\Http\Controllers;


class VerifyCodeController extends  Controller
{

   public function sendCode()
   {
       $code =  app('code')->getCode()->sendCode();

   }

}