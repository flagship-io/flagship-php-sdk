<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

class Segment extends HitAbstract
{
    const SL_MESSAGE_ERROR = "Sl value must be an associative array";
    const ERROR_MESSAGE  = 'sl is required';
    /**
     * @var array
     */
    protected $sl;

    /**
     * @return array
     */
    public function getSl()
    {
        return $this->sl;
    }

    /**
     * @param array $sl
     * @return Segment
     */
    public function setSl(array $sl)
    {
        if (!$this->isAssoc($sl)){
            $this->logError($this->getConfig(),self::SL_MESSAGE_ERROR,[FlagshipConstant::TAG=>__FUNCTION__]);
            return $this;
        }
        $this->sl = $sl;
        return $this;
    }

    public function __construct(array $sl)
    {
        parent::__construct(HitType::SEGMENT);
        $this->setSl($sl);
    }

    public function isAssoc(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $arrayParent = parent::toArray();
        $context = [];
        foreach ($this->sl as  $key=>$value){
            $context[$key] = is_string($value) ? $value : json_encode($value);
        }
        $arrayParent[FlagshipConstant::SL_API_ITEM] = $context;
        return $arrayParent;
    }

    /**
     * @inheritDoc
     */
    public function isReady()
    {
        return parent::isReady() && $this->getSl();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return self::ERROR_MESSAGE;
    }
}