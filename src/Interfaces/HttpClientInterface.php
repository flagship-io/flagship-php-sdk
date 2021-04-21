<?php

namespace Flagship\Interfaces;

interface HttpClientInterface
{
    public function setHeaders($headers);

    public function setTimeout($seconds);

    public function get($url, $data = []);

    public function post($url, array $params = [], array $data = []);

    /**
     * Return new Curl instance
     * @return HttpClientInterface
     */
    public static function create();
}
