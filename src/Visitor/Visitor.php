<?php

namespace Flagship\Visitor;

use Flagship\Config\FlagshipConfig;
use Flagship\Hit\HitAbstract;
use Flagship\Traits\LogTrait;
use JsonSerializable;

class Visitor implements VisitorInterface, JsonSerializable
{
    use LogTrait;

    /**
     * @var VisitorDelegate
     */
    private $visitorDelegate;


    /**
     * Create a new visitor.
     *
     * @param VisitorDelegate $visitorDelegate
     */
    public function __construct(VisitorDelegate $visitorDelegate)
    {
        $this->visitorDelegate = $visitorDelegate;
    }


    /**
     * @return VisitorDelegate
     */
    private function getVisitorDelegate()
    {
        return $this->visitorDelegate;
    }


    /**
     *
     * @return FlagshipConfig
     */
    public function getConfig()
    {
        return $this->getVisitorDelegate()->getConfig();
    }


    /**
     * @inheritDoc
     */
    public function getVisitorId()
    {
        return $this->getVisitorDelegate()->getVisitorId();
    }


    /**
     * @inheritDoc
     */
    public function setVisitorId($visitorId)
    {
        $this->getVisitorDelegate()->setVisitorId($visitorId);
        return $this;
    }


    /**
     *@inheritDoc
     */
    public function hasConsented()
    {
        return $this->getVisitorDelegate()->hasConsented();
    }


    /**
     * @inheritDoc
     */
    public function setConsent($hasConsented)
    {
        $this->getVisitorDelegate()->setConsent($hasConsented);
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getContext()
    {
        return $this->getVisitorDelegate()->getContext();
    }


    /**
     * @inheritDoc
     */
    public function setContext($context)
    {
        $this->getVisitorDelegate()->setContext($context);
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getAnonymousId()
    {
        return $this->getVisitorDelegate()->getAnonymousId();
    }


    /**
     * @inheritDoc
     */
    public function updateContext($key, $value)
    {
        $this->getVisitorDelegate()->updateContext($key, $value);
    }


    /**
     * @inheritDoc
     */
    public function updateContextCollection(array $context)
    {
        $this->getVisitorDelegate()->updateContextCollection($context);
    }


    /**
     * @inheritDoc
     */
    public function clearContext()
    {
        $this->getVisitorDelegate()->clearContext();
    }


    /**
     * @inheritDoc
     */
    public function authenticate($visitorId)
    {
        $this->getVisitorDelegate()->authenticate($visitorId);
    }


    /**
     * @inheritDoc
     */
    public function unauthenticate()
    {
        $this->getVisitorDelegate()->unauthenticate();
    }


    /**
     * @inheritDoc
     */
    public function sendHit(HitAbstract $hit)
    {
        $this->getVisitorDelegate()->sendHit($hit);
    }


    /**
     * @inheritDoc
     * @return     mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->getVisitorDelegate()->jsonSerialize();
    }


    /**
     * @inheritDoc
     */
    public function fetchFlags()
    {
        $this->visitorDelegate->fetchFlags();
    }


    /**
     * @inheritDoc
     */
    public function getFlag($key, $defaultValue)
    {
        return $this->visitorDelegate->getFlag($key, $defaultValue);
    }


    /**
     * @inheritDoc
     */
    public function getFlagsDTO()
    {
        return $this->visitorDelegate->getFlagsDTO();
    }

    /**
     * @inheritDoc
     */
    public function getFetchStatus()
    {
        return $this->visitorDelegate->getFetchStatus();
    }

    /**
     * @inheritDoc
     */
    public function setOnFetchFlagsStatusChanged(callable $onFetchFlagsStatusChanged)
    {
        $this->visitorDelegate->setOnFetchFlagsStatusChanged($onFetchFlagsStatusChanged);
    }
}
