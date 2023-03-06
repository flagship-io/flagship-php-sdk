<?php

namespace Flagship\Visitor;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;
use Flagship\Flag\Flag;
use Flagship\Flag\FlagMetadata;
use Flagship\Hit\HitAbstract;
use Flagship\Model\FlagDTO;
use Flagship\Traits\Guid;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\ContainerInterface;

class VisitorDelegate extends VisitorAbstract
{
    use Guid;


    /**
     * Create a new VisitorDelegate.
     *
     * @param ContainerInterface $dependencyIContainer
     * @param ConfigManager      $configManager
     * @param string             $visitorId             visitor unique identifier.
     * @param boolean            $isAuthenticated
     * @param array              $context     visitor context. e.g: ["age"=>42, "isVip"=>true, "country"=>"UK"]
     * @param boolean            $hasConsented
     */
    public function __construct(
        ContainerInterface $dependencyIContainer,
        ConfigManager $configManager,
        $visitorId,
        $isAuthenticated = false,
        array $context = [],
        $hasConsented = false
    ) {
        parent::__construct();
        $this->setDependencyIContainer($dependencyIContainer);
        $this->setConfig($configManager->getConfig());
        $this->setVisitorId($visitorId ?: $this->newGuid());
        $this->setContext($context);
        $this->setConfigManager($configManager);
        $this->loadPredefinedContext();

        if ($isAuthenticated && $this->getConfig()->getDecisionMode() == DecisionMode::DECISION_API) {
            $anonymousId = $this->newGuid();
            $this->setAnonymousId($anonymousId);
        }

        $this->setConsent($hasConsented);
        $this->getStrategy()->lookupVisitor();
    }//end __construct()


    /**
     * @return void
     */
    private function loadPredefinedContext()
    {
        $defaultContext = [FlagshipContext::OS_NAME => PHP_OS];
        $this->updateContextCollection($defaultContext);

        $this->context[FlagshipConstant::FS_CLIENT]  = FlagshipConstant::SDK_LANGUAGE;
        $this->context[FlagshipConstant::FS_VERSION] = FlagshipConstant::SDK_VERSION;
        $this->context[FlagshipConstant::FS_USERS]   = $this->getVisitorId();
    }//end loadPredefinedContext()


    /**
     * @inheritDoc
     */
    public function updateContext($key, $value)
    {
        $this->getStrategy()->updateContext($key, $value);
    }//end updateContext()


    /**
     * @inheritDoc
     */
    public function updateContextCollection(array $context)
    {
        $this->getStrategy()->updateContextCollection($context);
    }//end updateContextCollection()


    /**
     * @inheritDoc
     */
    public function clearContext()
    {
        $this->getStrategy()->clearContext();
        $this->loadPredefinedContext();
    }//end clearContext()


    /**
     * @inheritDoc
     */
    public function authenticate($visitorId)
    {
        $this->getStrategy()->authenticate($visitorId);
        $this->loadPredefinedContext();
    }//end authenticate()


    /**
     * @inheritDoc
     */
    public function unauthenticate()
    {
        $this->getStrategy()->unauthenticate();
        $this->loadPredefinedContext();
    }//end unauthenticate()


    /**
     * @inheritDoc
     */
    public function getModification($key, $defaultValue, $activate = false)
    {
        return $this->getStrategy()->getModification($key, $defaultValue, $activate);
    }//end getModification()


    /**
     * @inheritDoc
     */
    public function getModificationInfo($key)
    {
        return $this->getStrategy()->getModificationInfo($key);
    }//end getModificationInfo()


    /**
     * @inheritDoc
     */
    public function synchronizeModifications()
    {
        $this->getStrategy()->synchronizeModifications();
        $this->getStrategy()->cacheVisitor();
    }//end synchronizeModifications()


    /**
     * @inheritDoc
     */
    public function activateModification($key)
    {
        $this->getStrategy()->activateModification($key);
    }//end activateModification()


    /**
     * @inheritDoc
     */
    public function sendHit(HitAbstract $hit)
    {
        $this->getStrategy()->sendHit($hit);
    }//end sendHit()


    public function fetchFlags()
    {
        $this->getStrategy()->fetchFlags();
        $this->getStrategy()->cacheVisitor();
    }//end fetchFlags()


    /**
     * @inheritDoc
     */
    public function userExposed($key, $defaultValue, FlagDTO $flag = null)
    {
        $this->getStrategy()->userExposed($key, $defaultValue, $flag);
    }//end userExposed()


    /**
     * @inheritDoc
     */
    public function getFlagValue($key, $defaultValue, FlagDTO $flag = null, $userExposed = true)
    {
        return $this->getStrategy()->getFlagValue($key, $defaultValue, $flag, $userExposed);
    }//end getFlagValue()


    /**
     * @inheritDoc
     */
    public function getFlagMetadata($key, FlagMetadata $metadata, $hasSameType)
    {
        return $this->getStrategy()->getFlagMetadata($key, $metadata, $hasSameType);
    }//end getFlagMetadata()

    /**
     * @inheritDoc
     */
    public function getFlag($key, $defaultValue)
    {
        return new Flag($key, $this, $defaultValue);
    }//end getFlag()
}//end class
