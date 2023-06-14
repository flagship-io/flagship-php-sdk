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
    } //end __construct()


    /**
     * @return VisitorDelegate
     */
    private function getVisitorDelegate()
    {
        return $this->visitorDelegate;
    } //end getVisitorDelegate()


    /**
     *
     * @return FlagshipConfig
     */
    public function getConfig()
    {
        return $this->getVisitorDelegate()->getConfig();
    } //end getConfig()


    /**
     * @inheritDoc
     */
    public function getVisitorId()
    {
        return $this->getVisitorDelegate()->getVisitorId();
    } //end getVisitorId()


    /**
     * Set visitor unique identifier
     *
     * @param  string $visitorId
     * @return Visitor
     */
    public function setVisitorId($visitorId)
    {
        $this->getVisitorDelegate()->setVisitorId($visitorId);
        return $this;
    } //end setVisitorId()


    /**
     * Return True if the visitor has consented for private data usage, otherwise return False.
     *
     * @return boolean
     */
    public function hasConsented()
    {
        return $this->getVisitorDelegate()->hasConsented();
    } //end hasConsented()


    /**
     * Set if visitor has consented for private data usage.
     *
     * @param  boolean $hasConsented True if the visitor has consented false otherwise.
     * @return $this
     */
    public function setConsent($hasConsented)
    {
        $this->getVisitorDelegate()->setConsent($hasConsented);
        return $this;
    } //end setConsent()


    /**
     * @inheritDoc
     */
    public function getContext()
    {
        return $this->getVisitorDelegate()->getContext();
    } //end getContext()


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
    } //end setContext()


    /**
     * @inheritDoc
     */
    public function getAnonymousId()
    {
        return $this->getVisitorDelegate()->getAnonymousId();
    } //end getAnonymousId()


    /**
     * @inheritDoc
     */
    public function updateContext($key, $value)
    {
        $this->getVisitorDelegate()->updateContext($key, $value);
    } //end updateContext()


    /**
     * @inheritDoc
     */
    public function updateContextCollection(array $context)
    {
        $this->getVisitorDelegate()->updateContextCollection($context);
    } //end updateContextCollection()


    /**
     * @inheritDoc
     */
    public function clearContext()
    {
        $this->getVisitorDelegate()->clearContext();
    } //end clearContext()


    /**
     * @inheritDoc
     */
    public function authenticate($visitorId)
    {
        $this->getVisitorDelegate()->authenticate($visitorId);
    } //end authenticate()


    /**
     * @inheritDoc
     */
    public function unauthenticate()
    {
        $this->getVisitorDelegate()->unauthenticate();
    } //end unauthenticate()


    /**
     * @inheritDoc
     */
    public function getModification($key, $defaultValue, $activate = false)
    {
        return $this->getVisitorDelegate()->getModification($key, $defaultValue, $activate);
    } //end getModification()


    /**
     * @inheritDoc
     */
    public function getModificationInfo($key)
    {
        return $this->getVisitorDelegate()->getModificationInfo($key);
    } //end getModificationInfo()


    /**
     * @inheritDoc
     */
    public function synchronizeModifications()
    {
        $this->getVisitorDelegate()->synchronizeModifications();
    } //end synchronizeModifications()


    /**
     * @inheritDoc
     */
    public function activateModification($key)
    {
        $this->getVisitorDelegate()->activateModification($key);
    } //end activateModification()


    /**
     * @inheritDoc
     */
    public function sendHit(HitAbstract $hit)
    {
        $this->getVisitorDelegate()->sendHit($hit);
    } //end sendHit()


    /**
     * @inheritDoc
     * @return     mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->getVisitorDelegate()->jsonSerialize();
    } //end jsonSerialize()


    /**
     * @inheritDoc
     */
    public function getModifications()
    {
        return $this->getVisitorDelegate()->getModifications();
    } //end getModifications()


    /**
     * @inheritDoc
     */
    public function fetchFlags()
    {
        $this->visitorDelegate->fetchFlags();
    } //end fetchFlags()


    /**
     * @inheritDoc
     */
    public function getFlag($key, $defaultValue)
    {
        return $this->visitorDelegate->getFlag($key, $defaultValue);
    } //end getFlag()


    /**
     * @inheritDoc
     */
    public function getFlagsDTO()
    {
        return $this->visitorDelegate->getFlagsDTO();
    } //end getFlagsDTO()
}//end class
