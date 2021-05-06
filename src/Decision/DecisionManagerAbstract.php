<?php

namespace Flagship\Decision;

use Flagship\Utils\HttpClientInterface;
use Flagship\Visitor;

abstract class DecisionManagerAbstract implements DecisionManagerInterface
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
     * @inheritDoc
     */
    abstract public function getCampaigns(Visitor $visitor);

    /**
     * @inheritDoc
     */
    abstract public function getModifications($campaigns);
}
