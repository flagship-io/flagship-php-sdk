<?php

namespace Flagship\Decision;

use Flagship\Model\Modification;
use Flagship\Visitor\VisitorAbstract;

interface DecisionManagerInterface
{
    /**
     * This function fetch campaigns modifications from the server according to the visitor context and
     * Return an array of Modification from all campaigns
     * @param VisitorAbstract $visitor
     * @return Modification[]
     */
    public function getCampaignModifications(VisitorAbstract $visitor);
}
