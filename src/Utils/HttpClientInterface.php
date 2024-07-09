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
     * @param  array $headers : Collection key, value of http header
     * @return HttpClientInterface
     */
    public function setHeaders(array $headers): HttpClientInterface;

    /**
     * set the Timeout
     *
     * @param int $seconds
     * @return HttpClientInterface
     */
    public function setTimeout(int $seconds = FlagshipConstant::REQUEST_TIME_OUT): HttpClientInterface;

    /**
     * send a http get request
     *
     * @param string $url
     * @param  array $params Collection key, value of http params
     * @return HttpResponse
     */
    public function get(string $url, array $params = []): HttpResponse;

    /**
     * send a http post request
     *
     * @param string $url
     * @param  array $query Collection key, value of http params
     * @param  array $data   Collection key, value of http post body
     * @return HttpResponse
     */
    public function post(string $url, array $query = [], array $data = []): HttpResponse;
}
