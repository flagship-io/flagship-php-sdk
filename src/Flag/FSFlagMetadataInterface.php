<?php

namespace Flagship\Flag;

use JsonSerializable;

interface FSFlagMetadataInterface extends JsonSerializable
{
    /**
     * @return string
     */
    public function getCampaignId(): string;

    /**
     * @return ?string
     */
    public function getCampaignName(): ?string;

    /**
     * @return string
     */
    public function getVariationGroupId(): string;

    /**
     * @return ?string
     */
    public function getVariationGroupName(): ?string;

    /**
     * @return string
     */
    public function getVariationId(): string;

    /**
     * @return ?string
     */
    public function getVariationName(): ?string;

    /**
     * @return bool
     */
    public function isReference(): bool;

    /**
     * @return string
     */
    public function getCampaignType(): string;

    /**
     * @return ?string
     */
    public function getSlug(): ?string;
}
