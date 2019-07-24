<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

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
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if($request->wantsJson() ||$request->expectsJson()){
            $e =  $this->prepareException($e);
            switch ($e){
                case $e instanceof  ValidationException:
                   $re = collect($e->errors())->values()->flatten(1)->toArray();
                    return  response()->json([
                        'msg' => $re[0],
                        'status' =>422 ,
                        'data' => null,
                    ],422);
                case  $e instanceof HttpResponseException:
                    return $e->getResponse();
                case  $e instanceof AuthenticationException:
                    return  response()->json([
                        'msg' =>'用户身份校验未通过',
                        'status' =>401 ,
                        'data' => null,
                    ],401);
                case   $this->isHttpException($e):
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
