<?php

namespace Flagship\Model;

/**
 * Class HttpResponse
 * @package Flagship\Model
 */
class HttpResponse
{
    private string|int $statusCode;
    private mixed $body;
    /**
     * @var array
     */
    private array $headers;

    /**
     * HttpResponse constructor.
     * @param string|int $statusCode
     * @param mixed $body
     * @param array $headers
     */
    public function __construct(
        string|int $statusCode,
        mixed $body,
        array $headers = []
    ) {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $headers;
    }

    /**
     * @return string|int
     */
    public function getStatusCode(): string|int
    {
        return $this->statusCode;
    }

    /**
     * @param  mixed $statusCode
     * @return HttpResponse
     */
    public function setStatusCode(mixed $statusCode): static
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody(): mixed
    {
        return $this->body;
    }

    /**
     * @param  mixed $body
     * @return HttpResponse
     */
    public function setBody(mixed $body): static
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
