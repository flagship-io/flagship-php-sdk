<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

class Consent extends HitAbstract
{
    const ERROR_MESSAGE  = 'visitorConsent is required';

    /**
     * @var boolean
     */
    private $visitorConsent;

    /**
     * @return boolean
     */
    public function getVisitorConsent()
    {
        return $this->visitorConsent;
    }

    /**
     * @param boolean $visitorConsent
     * @return Consent
     */
    public function setVisitorConsent($visitorConsent)
    {
        $this->visitorConsent = $visitorConsent;
        return $this;
    }

    /**
     * @param $visitorConsent string
     */
    public function __construct($visitorConsent)
    {
        parent::__construct(HitType::ACTIVATE);
        $this->visitorConsent = $visitorConsent;
    }

    public function toArray()
    {
        $arrayParent = parent::toArray();
        $arrayParent[FlagshipConstant::VISITOR_CONSENT] = $this->visitorConsent;
        return $arrayParent;
    }

    public function isReady()
    {
        return parent::isReady() && $this->getVisitorConsent();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return self::ERROR_MESSAGE;
    }
}