<?php

namespace Flagship\Flag;

use Flagship\Model\FlagDTO;
use Flagship\Traits\HasSameTypeTrait;
use Flagship\Visitor\VisitorAbstract;

class Flag implements FlagInterface
{
    use HasSameTypeTrait;

    /**
     * @var string
     */
    private $key;

    /**
     * @var VisitorAbstract
     */
    private $visitorDelegate;

    /**
     * @var mixed
     */
    private $defaultValue;


    /***
     * @param string          $key
     * @param VisitorAbstract $visitorDelegate
     * @param mixed           $defaultValue
     */
    public function __construct(
        $key,
        VisitorAbstract $visitorDelegate,
        $defaultValue
    ) {
        $this->key             = $key;
        $this->visitorDelegate = $visitorDelegate;

        $this->defaultValue = $defaultValue;

    }//end __construct()


    /**
     * @param  $key
     * @return FlagDTO|null
     */
    protected function findFlagDTO($key)
    {
        foreach ($this->visitorDelegate->getFlagsDTO() as $flagDTO) {
            if ($flagDTO->getKey() === $key) {
                return $flagDTO;
            }
        }

        return null;

    }//end findFlagDTO()


    /**
     * @inheritDoc
     */
    public function getValue($userExposed=true)
    {
        $flagDTO = $this->findFlagDTO($this->key);
        return $this->visitorDelegate->getFlagValue($this->key, $this->defaultValue, $flagDTO, $userExposed);

    }//end getValue()


    /**
     * @inheritDoc
     */
    public function exists()
    {
        $flagDTO = $this->findFlagDTO($this->key);
        return $flagDTO && $flagDTO->getCampaignId() && $flagDTO->getVariationId() && $flagDTO->getVariationGroupId();

    }//end exists()


    /**
     * @inheritDoc
     */
    public function userExposed()
    {
        $flagDTO = $this->findFlagDTO($this->key);
        $this->visitorDelegate->userExposed(
            $this->key,
            $this->defaultValue,
            $flagDTO
        );

    }//end userExposed()


    /**
     * @inheritDoc
     */
    public function getMetadata()
    {
        $flagDTO  = $this->findFlagDTO($this->key);
        $metadata = new FlagMetadata(
            $flagDTO ? $flagDTO->getCampaignId() : '',
            $flagDTO ? $flagDTO->getVariationGroupId() : '',
            $flagDTO ? $flagDTO->getVariationId() : '',
            $flagDTO ? $flagDTO->getIsReference() : false,
            $flagDTO ? $flagDTO->getCampaignType() : '',
            $flagDTO ? $flagDTO->getSlug() : null
        );

        if (!$flagDTO) {
            return $metadata;
        }

        return $this->visitorDelegate->getFlagMetadata(
            $this->key,
            $metadata,
            !$flagDTO->getValue() || $this->hasSameType($flagDTO->getValue(), $this->defaultValue)
        );

    }//end getMetadata()


}//end class
