<?php

namespace Flagship\Api;

use Flagship\Hit\HitAbstract;
use Flagship\Model\Modification;
use Flagship\Utils\HttpClientInterface;
use Flagship\Visitor;

/**
 * Class TrackingManagerAbstract
 * @package Flagship\Api
 */
abstract class TrackingManagerAbstract
{
    /**
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * ApiManager constructor.
     *
     * @param HttpClientInterface $httpClient
     */
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Send to that this user has seen this modification
     *
     * @param  Visitor      $visitor
     * @param  Modification $modification
     * @return bool
     */
    abstract public function sendActive(Visitor $visitor, Modification $modification);

    /**
     * @param  HitAbstract $hit
     * @return mixed
     */
    abstract public function sendHit(HitAbstract $hit);
}
