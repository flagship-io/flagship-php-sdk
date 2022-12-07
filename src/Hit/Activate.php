<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

class Activate extends HitAbstract
{
    const ERROR_MESSAGE  = 'variationId and variationGroupId are required';
    private $variationGroupId;
    private $variationId;

    /**
     * @param $variationGroupId
     * @param $variationId
     */
    public  function __construct($variationGroupId, $variationId)
    {
        parent::__construct(HitType::ACTIVATE);
        $this->variationGroupId = $variationGroupId;
        $this->variationId = $variationId;
    }

    /**
     * @return mixed
     */
    public function getVariationGroupId()
    {
        return $this->variationGroupId;
    }

    /**
     * @param mixed $variationGroupId
     * @return Activate
     */
    public function setVariationGroupId($variationGroupId)
    {
        $this->variationGroupId = $variationGroupId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVariationId()
    {
        return $this->variationId;
    }

    /**
     * @param mixed $variationId
     * @return Activate
     */
    public function setVariationId($variationId)
    {
        $this->variationId = $variationId;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $apiKeys = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $this->getVisitorId(),
            FlagshipConstant::VARIATION_ID_API_ITEM => $this->getVisitorId(),
            FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $this->getVariationGroupId(),
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $this->config->getEnvId(),
            FlagshipConstant::ANONYMOUS_ID => null
        ];

        if ($this->getVisitorId() && $this->getAnonymousId()){
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