<?php

namespace Flagship\Visitor;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;
use Flagship\Enum\FlagSyncStatus;
use Flagship\Flag\Flag;
use Flagship\Flag\FlagMetadata;
use Flagship\Hit\HitAbstract;
use Flagship\Model\FlagDTO;
use Flagship\Traits\Guid;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\ContainerInterface;

class VisitorDelegate extends VisitorAbstract
{



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
        $this->setFlagSyncStatus(FlagSyncStatus::CREATED);
    }

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
    }


    /**
     * @inheritDoc
     */
    public function unauthenticate()
    {
        $this->getStrategy()->unauthenticate();
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
        $this->getStrategy()->cacheVisitor();
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


    public function fetchFlags()
    {
        $this->getStrategy()->fetchFlags();
        $this->getStrategy()->cacheVisitor();
    }


    /**
     * @inheritDoc
     */
    public function visitorExposed($key, $defaultValue, FlagDTO $flag = null)
    {
        $this->getStrategy()->visitorExposed($key, $defaultValue, $flag);
    }


    /**
     * @inheritDoc
     */
    public function getFlagValue($key, $defaultValue, FlagDTO $flag = null, $userExposed = true)
    {
        return $this->getStrategy()->getFlagValue($key, $defaultValue, $flag, $userExposed);
    }


    /**
     * @inheritDoc
     */
    public function getFlagMetadata($key, FlagMetadata $metadata, $hasSameType)
    {
        return $this->getStrategy()->getFlagMetadata($key, $metadata, $hasSameType);
    }

    /**
     * @inheritDoc
     */
    public function getFlag($key, $defaultValue)
    {
        if ($this->getFlagSyncStatus() !== FlagSyncStatus::FLAGS_FETCHED) {
            $this->logWarningSprintf(
                $this->getConfig(),
                FlagshipConstant::GET_FLAG,
                $this->flagSyncStatusMessage($this->getFlagSyncStatus()),
                [$this->getVisitorId(), $key]
            );
        }
        return new Flag($key, $this, $defaultValue);
    }

    /**
     * @param $status string
     * @return string
     */
    protected function flagSyncStatusMessage($status)
    {
        $message = "";
        $commonMessage = 'without calling `fetchFlags` method afterwards, the value of the flag `%s` may be outdated';
        switch ($status) {
            case FlagSyncStatus::CREATED:
                $message = "Visitor `%s` has been created {$commonMessage}`";
                break;
            case FlagSyncStatus::CONTEXT_UPDATED:
                $message = "Visitor context for visitor `%s` has been updated {$commonMessage}";
                break;
            case FlagSyncStatus::AUTHENTICATED:
                $message = "Visitor `%s` has been authenticated {$commonMessage}";
                break;
            case FlagSyncStatus::UNAUTHENTICATED:
                $message = "Visitor `%s` has been unauthenticated {$commonMessage}";
                break;
        }
        return $message;
    }
}
