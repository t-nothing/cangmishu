<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    /**
     * The recommended response to send to the client.
     *
     * @var \Symfony\Component\HttpFoundation\Response|null
     */
    public $response;

    /**
     * Create a new exception instance.
     *
     * @param  string  $message
     */
    public function __construct($message = '')
    {
        $locale = app('translator')->getLocale();
        $msg = [
            'en' => 'fail',
            'cn' => '失败',
            'zh-CN' =>'失败'
        ];
        parent::__construct($msg[$locale]);

        $this->response = formatRet(500, $message);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return $this->getResponse();
    }

    /**
     * Get the underlying response instance.
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
