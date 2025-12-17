<?php

namespace Flagship\Utils;

use Flagship\Enum\FlagshipConstant;
use Flagship\Model\HttpResponse;

/**
 * Interface HttpClientInterface
 *
 * @package Flagship\Interfaces
 */
interface HttpClientInterface
{
    /**
     * Add extra headers to include in the request.
     *
     * @param  array<string, string> $headers : Collection key, value of http header
     * @return HttpClientInterface
     */
    public function setHeaders(array $headers): HttpClientInterface;

    /**
     * set the Timeout
     *
     * @param float $seconds
     * @return HttpClientInterface
     */
    public function setTimeout(float $seconds = FlagshipConstant::REQUEST_TIME_OUT): HttpClientInterface;

    /**
     * send a http get request
     *
     * @param string $url
     * @param  array<mixed> $params Collection key, value of http params
     * @return HttpResponse
     */
    public function get(string $url, array $params = []): HttpResponse;

    /**
     * send a http post request
     *
     * @param string $url
     * @param  array<mixed> $query Collection key, value of http params
     * @param  array<mixed> $data   Collection key, value of http post body
     * @return HttpResponse
     */
    public function post(string $url, array $query = [], array $data = []): HttpResponse;
}
