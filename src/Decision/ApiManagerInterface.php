<?php

namespace Flagship\Decision;

use Flagship\Model\Modification;
use Flagship\Utils\HttpClientInterface;
use Flagship\Visitor;

/**
 * This class manage all http calls to Decision api
 *
 * @package Flagship\Decision
 */
interface ApiManagerInterface
{
    /**
     * This function will fetch campaigns modifications from the server according to the visitor context and
     * return an associative array of campaigns
     *
     * @param  Visitor             $visitor
     * @param  HttpClientInterface $httpClient
     * @return array return an associative array of campaigns
     */
    public function getCampaigns(Visitor $visitor, HttpClientInterface $httpClient);

    /**
     * This function will fetch campaigns modifications from the server according to the visitor context and
     * Return an array of Modification from all campaigns
     *
     * @param  Visitor             $visitor
     * @param  HttpClientInterface $httpClient
     * @return Modification[] Return an array of Modification
     */
    public function getCampaignsModifications(Visitor $visitor, HttpClientInterface $httpClient);
}
