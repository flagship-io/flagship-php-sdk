<?php

namespace Flagship\Flag;

interface FlagMetadataInterface
{
    /**
     * @return string
     */
    public function getCampaignId();

    /**
     * @return string
     */
    public function getVariationGroupId();

    /**
     * @return string
     */
    public function getVariationId();

    /**
     * @return bool
     */
    public function isReference();

    /**
     * @return string
     */
    public function getCampaignType();

    /**
     * @return string
     */
    public function getSlug();
}
