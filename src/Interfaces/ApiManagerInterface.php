<?php

namespace Flagship\Interfaces;

use Flagship\Model\Modification;
use Flagship\Visitor;

/**
 * This class manage all http calls to Decision api
 * @package Flagship\Decision
 */
interface ApiManagerInterface
{
    /**
     * This function will fetch campaigns modifications from the server according to the visitor context.
     * @param Visitor $visitor
     * @param HttpClientInterface $httpClient
     * @return array
     */
    public function getCampaigns(Visitor $visitor, HttpClientInterface $httpClient);

    /**
     * @param Visitor $visitor
     * @param HttpClientInterface $httpClient
     * @return Modification[]
     */
    public function getCampaignsModifications(Visitor $visitor, HttpClientInterface $httpClient);

    /**
     * @param array $campaigns
     * @return Modification[]
     */
    public function getAllModifications(array $campaigns);
}
