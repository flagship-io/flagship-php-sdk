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
    public function setHeaders(array $headers);

    /**
     * set the Timeout
     *
     * @param  $seconds
     * @return HttpClientInterface
     */
    public function setTimeout($seconds = FlagshipConstant::REQUEST_TIME_OUT);

    /**
     * send a http get request
     *
     * @param  $url
     * @param  array $params Collection key, value of http params
     * @return HttpResponse
     */
    public function get($url, array $params = []);

    /**
     * send a http post request
     *
     * @param  $url
     * @param  array $query Collection key, value of http params
     * @param  array $data   Collection key, value of http post body
     * @return HttpResponse
     */
    public function post($url, array $query = [], array $data = []);
}
