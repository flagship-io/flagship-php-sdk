<?php

namespace Flagship\Model;

/**
 * This class is representation of modification to display
 *
 * @package Flagship\Model
 */
class Modification
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $campaignId;

    /**
     * @var string
     */
    private $variationGroupId;

    /**
     * @var string
     */
    private $variationId;

    /**
     * @var string
     */
    private $isReference;

    /**
     * @var string|bool|numeric
     */    
    private $value;

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param  string $key
     * @return Modification
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * @param  string $campaignId
     * @return Modification
     */
    public function setCampaignId($campaignId)
    {
        $this->campaignId = $campaignId;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariationGroupId()
    {
        return $this->variationGroupId;
    }

    /**
     * @param  string $variationGroupId
     * @return Modification
     */
    public function setVariationGroupId($variationGroupId)
    {
        $this->variationGroupId = $variationGroupId;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariationId()
    {
        return $this->variationId;
    }

    /**
     * @param  string $variationId
     * @return Modification
     */
    public function setVariationId($variationId)
    {
        $this->variationId = $variationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getIsReference()
    {
        return $this->isReference;
    }

    /**
     * @param  string $isReference
     * @return Modification
     */
    public function setIsReference($isReference)
    {
        $this->isReference = $isReference;
        return $this;
    }

    /**
     * @return string|bool|numeric
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param  string|bool|numeric $value
     * @return Modification
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}
