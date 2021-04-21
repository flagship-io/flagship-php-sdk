<?php

namespace Flagship\Interfaces;

use Flagship\Visitor;

interface ApiManagerInterface
{
    /**
     * This function will fetch campaigns modifications from the server according to the visitor context.
     * @param Visitor $visitor
     * @param HttpClientInterface $curl
     * @return array
     */
    public function getCampaigns(Visitor $visitor, HttpClientInterface $curl);
}
