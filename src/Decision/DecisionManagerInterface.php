<?php


namespace Flagship\Decision;


use Flagship\Model\Modification;
use Flagship\Visitor;

interface DecisionManagerInterface
{
    /**
     * This function will fetch campaigns modifications from the server according to the visitor context and
     * return an associative array of campaigns
     *
     * @param  Visitor $visitor
     * @return array return an associative array of campaigns
     */
    public function getCampaigns(Visitor $visitor);

    /**
     * Return an array of Modification from all campaigns
     *
     * @param  $campaigns
     * @return Modification[] Return an array of Modification
     */
    public function getModifications($campaigns);
}
