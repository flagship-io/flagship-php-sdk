<?php

namespace Flagship\Visitor;

use Flagship\Config\FlagshipConfig;
use Flagship\Hit\HitAbstract;
use Flagship\Model\FlagDTO;
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
     * visitor unique identifier
     * @return string
     */
    public function getVisitorId()
    {
        return $this->getVisitorDelegate()->getVisitorId();
    }

    /**
     * Set visitor unique identifier
     * @param string $visitorId
     * @return Visitor
     */
    public function setVisitorId($visitorId)
    {
        $this->getVisitorDelegate()->setVisitorId($visitorId);
        return $this;
    }

    /**
     * Return True if the visitor has consented for private data usage, otherwise return False.
     * @return bool
     */
    public function hasConsented()
    {
        return $this->getVisitorDelegate()->hasConsented();
    }

    /**
     * Set if visitor has consented for private data usage.
     * @param bool $hasConsented True if the visitor has consented false otherwise.
     * @return $this
     */
    public function setConsent($hasConsented)
    {
        $this->getVisitorDelegate()->setConsent($hasConsented);
        return $this;
    }

    /**
     * Get the current context
     * @return array
     */
    public function getContext()
    {
        return $this->getVisitorDelegate()->getContext();
    }

    /**
    /**
     * Clear the current context and set a new context value
     *
     * @param  array $context : collection of keys, values. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
     * @return Visitor
     */
    public function setContext($context)
    {
        $this->getVisitorDelegate()->setContext($context);
        return $this;
    }

    /**
     * visitor anonymous id
     * @return string
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
    public function getModification($key, $defaultValue, $activate = false)
    {
        return $this->getVisitorDelegate()->getModification($key, $defaultValue, $activate);
    }

    /**
     * @inheritDoc
     */
    public function getModificationInfo($key)
    {
        return $this->getVisitorDelegate()->getModificationInfo($key);
    }

    /**
     * @inheritDoc
     */
    public function synchronizeModifications()
    {
        $this->getVisitorDelegate()->synchronizeModifications();
    }


    /**
     * @inheritDoc
     */
    public function activateModification($key)
    {
        $this->getVisitorDelegate()->activateModification($key);
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
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->getVisitorDelegate()->jsonSerialize();
    }

    /**
     * @inheritDoc
     */
    public function getModifications()
    {
        return $this->getVisitorDelegate()->getModifications();
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
}
