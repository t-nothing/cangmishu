<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        AuthenticationException::class,
        BusinessException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof AuthenticationException) {
            return formatRet(401, $e->getMessage(), [], 401);
        }

        if ($e instanceof AuthorizationException) {
            return formatRet(403, $e->getMessage(), [], 403);
        }

        if ($e instanceof ModelNotFoundException) {
            $msg = isset($e->model) && is_string($e->model)
                ? trans('models.'.$e->model) . '不存在'
                : trans('messages.404NotFound');

            return formatRet(404, $msg, [], 404);
        }

        if ($e instanceof BusinessException) {
            return $e->render();
        }

        return parent::render($request, $e);
    }
}
