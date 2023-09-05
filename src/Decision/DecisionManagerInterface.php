<?php

namespace Flagship\Decision;

use Flagship\Model\FlagDTO;
use Flagship\Visitor\VisitorAbstract;

interface DecisionManagerInterface
{
    /**
     * This function fetch campaigns modifications from the server according to the visitor context and
     * Return an array of Modification from all campaigns
     * @param VisitorAbstract $visitor
     * @return FlagDTO[]
     */
    public function getCampaignModifications(VisitorAbstract $visitor);

    /**
     * @param VisitorAbstract $visitor
     * @return array
     */
    public function getCampaigns(VisitorAbstract $visitor);

    /**
     * @param $campaigns
     * @return FlagDTO[]
     */
    public function getModifications($campaigns);

    public function getTroubleshootingData();
}
