<?php

namespace Flagship\Decision;

use Flagship\Model\FlagDTO;
use Flagship\Model\TroubleshootingData;
use Flagship\Visitor\VisitorAbstract;

interface DecisionManagerInterface
{
    /**
     * This function fetch campaigns flags from the server according to the visitor context and
     * Return an array of flags from all campaigns
     * @param VisitorAbstract $visitor
     * @return FlagDTO[]
     */
    public function getCampaignFlags(VisitorAbstract $visitor): array;

    /**
     * @param VisitorAbstract $visitor
     * @return array|null
     */
    public function getCampaigns(VisitorAbstract $visitor): array|null;

    /**
     * @param $campaigns
     * @return FlagDTO[]
     */
    public function getFlagsData($campaigns): array;

    public function getTroubleshootingData(): ?TroubleshootingData;
}
