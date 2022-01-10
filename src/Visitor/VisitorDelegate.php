<?php

namespace Flagship\Visitor;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;
use Flagship\Hit\HitAbstract;
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
     * @param ConfigManager $configManager
     * @param string $visitorId : visitor unique identifier.
     * @param bool $isAuthenticated
     * @param array $context : visitor context. e.g: ["age"=>42, "isVip"=>true, "country"=>"UK"]
     */
    public function __construct(
        ContainerInterface $dependencyIContainer,
        ConfigManager $configManager,
        $visitorId,
        $isAuthenticated = false,
        array $context = [],
        $hasConsented = false
    ) {
        $this->setDependencyIContainer($dependencyIContainer);
        $this->setConfig($configManager->getConfig());
        $this->setVisitorId($visitorId);
        $this->setContext($context);
        $this->setConfigManager($configManager);
        $this->loadPredefinedContext();

        if (!$hasConsented) {
            $this->setConsent($hasConsented);
        }
        $this->hasConsented = $hasConsented;

        if ($isAuthenticated && $this->getConfig()->getDecisionMode() == DecisionMode::DECISION_API) {
            $anonymousId  = $this->newGuid();
            $this->setAnonymousId($anonymousId);
        }
    }

    private function loadPredefinedContext()
    {
        $defaultContext = [
            FlagshipContext::OS_NAME => PHP_OS,
        ];
        $this->updateContextCollection($defaultContext);

        $this->context[FlagshipConstant::FS_CLIENT] = FlagshipConstant::SDK_LANGUAGE;
        $this->context[FlagshipConstant::FS_VERSION] = FlagshipConstant::SDK_VERSION;
        $this->context[FlagshipConstant::FS_USERS] = $this->getVisitorId();
    }

    /**
     * @inheritDoc
     */
    public function updateContext($key, $value)
    {
        $this->getStrategy()->updateContext($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function updateContextCollection(array $context)
    {
        $this->getStrategy()->updateContextCollection($context);
    }

    /**
     * @inheritDoc
     */
    public function clearContext()
    {
        $this->getStrategy()->clearContext();
        $this->loadPredefinedContext();
    }

    /**
     * @inheritDoc
     */
    public function authenticate($visitorId)
    {
        $this->getStrategy()->authenticate($visitorId);
        $this->loadPredefinedContext();
    }

    /**
     * @inheritDoc
     */
    public function unauthenticate()
    {
        $this->getStrategy()->unauthenticate();
        $this->loadPredefinedContext();
    }

    /**
     * @inheritDoc
     */
    public function getModification($key, $defaultValue, $activate = false)
    {
        return $this->getStrategy()->getModification($key, $defaultValue, $activate);
    }

    /**
     * @inheritDoc
     */
    public function getModificationInfo($key)
    {
        return $this->getStrategy()->getModificationInfo($key);
    }

    /**
     * @inheritDoc
     */
    public function synchronizeModifications()
    {
        $this->getStrategy()->synchronizeModifications();
    }


    /**
     * @inheritDoc
     */
    public function activateModification($key)
    {
        $this->getStrategy()->activateModification($key);
    }

    /**
     * @inheritDoc
     */
    public function sendHit(HitAbstract $hit)
    {
        $this->getStrategy()->sendHit($hit);
    }
}
