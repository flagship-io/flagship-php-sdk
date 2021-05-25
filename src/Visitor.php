<?php

namespace Flagship;

use Flagship\Hit\HitAbstract;
use Flagship\Traits\LogTrait;
use Flagship\Visitor\VisitorDelegate;
use Flagship\Visitor\VisitorInterface;
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

    private function getVisitorDelegate()
    {
        return $this->visitorDelegate;
    }

    /**
     * @return FlagshipConfig
     */
    public function getConfig()
    {
        return $this->getVisitorDelegate()->getConfig();
    }

    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->getVisitorDelegate()->getVisitorId();
    }

    /**
     * @param string $visitorId
     * @return Visitor
     */
    public function setVisitorId($visitorId)
    {
        $this->getVisitorDelegate()->setVisitorId($visitorId);
        return $this;
    }

    /**
     * Return True or False if the visitor has consented for private data usage.
     * @return bool
     */
    public function hasConsented()
    {
        return $this->getVisitorDelegate()->hasConsented();
    }

    /**
     * Set if visitor has consented for private data usage.
     * @param bool $hasConsented True if the visitor has consented false otherwise.
     */
    public function setConsent($hasConsented)
    {
        $this->getVisitorDelegate()->setConsent($hasConsented);
    }

    /**
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
    public function synchronizedModifications()
    {
        $this->getVisitorDelegate()->synchronizedModifications();
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
     */
    public function jsonSerialize()
    {
        return $this->getVisitorDelegate()->jsonSerialize();
    }
}
