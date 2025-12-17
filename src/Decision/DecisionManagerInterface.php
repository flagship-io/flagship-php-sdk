<?php

namespace Flagship\Decision;

use Flagship\Model\FlagDTO;
use Flagship\Model\CampaignDTO;
use Flagship\Visitor\VisitorAbstract;
use Flagship\Model\TroubleshootingData;

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
     * @return CampaignDTO[]|null
     */
    public function getCampaigns(VisitorAbstract $visitor): array|null;

    /**
     * @param CampaignDTO[] $campaigns
     * @return FlagDTO[]
     */
    public function getFlagsData(array $campaigns): array;

    public function getTroubleshootingData(): ?TroubleshootingData;
}
