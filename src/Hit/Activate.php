<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;
use Flagship\Flag\FlagMetadata;
use Flagship\Flag\FlagMetadataInterface;

class Activate extends HitAbstract
{
    const ERROR_MESSAGE  = 'variationId and variationGroupId are required';

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
    private $flagKey;

    /**
     * @var string
     */
    private $flagValue;

    /**
     * @var array
     */
    private $visitorContext;

    /**
     * @var FlagMetadataInterface
     */
    private $flagMetadata;

    public static function getClassName()
    {
        return __CLASS__;
    }



    /**
     * @param string $variationGroupId
     * @param string $variationId
     */
    public function __construct($variationGroupId, $variationId)
    {
        parent::__construct(HitType::ACTIVATE);
        $this->variationGroupId = $variationGroupId;
        $this->variationId = $variationId;
    }

    /**
     * @return string
     */
    public function getVariationGroupId()
    {
        return $this->variationGroupId;
    }

    /**
     * @param string $variationGroupId
     * @return Activate
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
     * @param string $variationId
     * @return Activate
     */
    public function setVariationId($variationId)
    {
        $this->variationId = $variationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagKey()
    {
        return $this->flagKey;
    }

    /**
     * @param string $flagKey
     * @return Activate
     */
    public function setFlagKey($flagKey)
    {
        $this->flagKey = $flagKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagValue()
    {
        return $this->flagValue;
    }

    /**
     * @param string $flagValue
     * @return Activate
     */
    public function setFlagValue($flagValue)
    {
        $this->flagValue = $flagValue;
        return $this;
    }

    /**
     * @return array
     */
    public function getVisitorContext()
    {
        return $this->visitorContext;
    }

    /**
     * @param array $visitorContext
     * @return Activate
     */
    public function setVisitorContext($visitorContext)
    {
        $this->visitorContext = $visitorContext;
        return $this;
    }

    /**
     * @return FlagMetadataInterface
     */
    public function getFlagMetadata()
    {
        return $this->flagMetadata;
    }

    /**
     * @param FlagMetadataInterface $flagMetadata
     * @return Activate
     */
    public function setFlagMetadata($flagMetadata)
    {
        $this->flagMetadata = $flagMetadata;
        return $this;
    }



    /**
     * @inheritDoc
     */
    public function toApiKeys()
    {
        $apiKeys = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $this->getVisitorId(),
            FlagshipConstant::VARIATION_ID_API_ITEM => $this->getVariationId(),
            FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $this->getVariationGroupId(),
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $this->config->getEnvId(),
            FlagshipConstant::ANONYMOUS_ID => null
        ];

        if ($this->getVisitorId() && $this->getAnonymousId()) {
            $apiKeys[FlagshipConstant::VISITOR_ID_API_ITEM]  = $this->getVisitorId();
            $apiKeys[FlagshipConstant::ANONYMOUS_ID] = $this->getAnonymousId();
        }

        return $apiKeys;
    }


    /**
     * @inheritDoc
     */
    public function isReady()
    {
        return parent::isReady() && $this->getVisitorId() && $this->getVariationGroupId();
    }


    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return self::ERROR_MESSAGE;
    }
}
