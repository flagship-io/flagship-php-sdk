<?php

namespace Flagship\Decision;

use Flagship\FlagshipConfig;
use Flagship\Model\Modification;
use Flagship\Utils\HttpClientInterface;
use Flagship\Visitor;

/**
 * This class manage all http calls to Decision api
 *
 * @package Flagship\Decision
 */
abstract class ApiManagerAbstract
{

    /**
     * @var FlagshipConfig
     */
    protected $config;
    /**
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * ApiManager constructor.
     *
     * @param FlagshipConfig $config    : configuration used when the visitor has been created.
     * @param HttpClientInterface $httpClient
     */
    public function __construct(FlagshipConfig $config, HttpClientInterface $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    /**
     * @return FlagshipConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param FlagshipConfig $config
     * @return ApiManagerAbstract
     */
    public function setConfig(FlagshipConfig $config)
    {
        $this->config = $config;
        return $this;
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
     * @param Visitor $visitor
     * @param Modification $modification
     * @return void
     */
    abstract public function sendActiveModification(Visitor $visitor, Modification $modification);

    /**
     * This function will fetch campaigns modifications from the server according to the visitor context and
     * return an associative array of campaigns
     *
     * @param Visitor $visitor
     * @return array return an associative array of campaigns
     */
    abstract public function getCampaigns(Visitor $visitor);

    /**
     * This function will fetch campaigns modifications from the server according to the visitor context and
     * Return an array of Modification from all campaigns
     *
     * @param Visitor $visitor
     * @return Modification[] Return an array of Modification
     */
    abstract public function getCampaignsModifications(Visitor $visitor);
}
