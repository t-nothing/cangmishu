<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        ValidationException::class,
        AuthenticationException::class,
        BusinessException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  Throwable  $e
     * @return void
     * @throws Throwable
     */
    public function report(Throwable  $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Throwable  $e
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        if($request->wantsJson() ||$request->expectsJson()){
            $e =  $this->prepareException($e);
            switch ($e){
                case  $e instanceof HttpResponseException:
                case $e instanceof  ValidationException:
                    $re = collect($e->errors())->values()->flatten(1)->toArray();
                    return  response()->json([
                        'msg' => $re[0],
                        'status' =>422 ,
                        'data' => null,
                    ],422);
                case  $e instanceof AuthenticationException:
                    return  response()->json([
                        'msg' =>'用户身份校验未通过',
                        'status' =>401 ,
                        'data' => null,
                    ],401);
                case   $this->isHttpException($e):
                    // if($exception->getStatusCode() == 403 && $request->is('horizon*')) return redirect('/admin-horizon-login');
                    return  response()->json([
                        'msg' =>$e->getMessage(),
                        'status' =>$e->getStatusCode() ,
                        'data' => null,
                    ],$e->getStatusCode());
                case $e instanceof BusinessException :
                    return $e->render();
                default:
                    return parent::render($request, $e);
            }
        }
        return parent::render($request, $e);
    }
}
