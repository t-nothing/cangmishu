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
     * @param  string  $errorBag
     * @return void
     */
    public function __construct($message = '')
    {
        parent::__construct('失败');

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
