<?php

namespace Flagship\Model;

/**
 * Class HttpResponse
 * @package Flagship\Model
 */
class HttpResponse
{
    private $statusCode;
    private $body;
    /**
     * @var array
     */
    private $headers;

    /**
     * HttpResponse constructor.
     * @param $statusCode
     * @param $body
     * @param array $headers
     */
    public function __construct($statusCode, $body, array $headers = [])
    {

        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $headers;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param  mixed $statusCode
     * @return HttpResponse
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param  mixed $body
     * @return HttpResponse
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
