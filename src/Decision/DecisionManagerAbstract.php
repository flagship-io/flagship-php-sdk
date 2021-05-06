<?php

namespace Flagship\Decision;

use Flagship\Utils\HttpClientInterface;
use Flagship\Visitor;

abstract class DecisionManagerAbstract implements DecisionManagerInterface
{
    /**
     * @var bool
     */
    protected $isPanicMode = false;
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
     * @return bool
     */
    public function getIsPanicMode()
    {
        return $this->isPanicMode;
    }

    /**
     * @param bool $isPanicMode
     * @return DecisionManagerAbstract
     */
    public function setIsPanicMode($isPanicMode)
    {
        $this->isPanicMode = $isPanicMode;
        return $this;
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
