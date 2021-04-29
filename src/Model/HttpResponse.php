<?php


namespace Flagship\Model;


class HttpResponse
{
    private $statusCode;
    private $body;

    public function __construct($statusCode, $body){

        $this->statusCode = $statusCode;
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $statusCode
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
     * @param mixed $body
     * @return HttpResponse
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }


}