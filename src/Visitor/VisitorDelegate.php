<?php

namespace Flagship\Visitor;

use Flagship\Enum\FSFetchReason;
use Flagship\Enum\FSFetchStatus;
use Flagship\Flag\FSFlag;
use Flagship\Flag\FSFlagCollection;
use Flagship\Model\FlagDTO;
use Flagship\Hit\HitAbstract;
use Flagship\Enum\DecisionMode;
use Flagship\Flag\FSFlagMetadata;
use Flagship\Utils\ConfigManager;
use Flagship\Enum\FlagshipContext;
use Flagship\Enum\FlagshipConstant;
use Flagship\Model\FetchFlagsStatus;
use Flagship\Utils\ContainerInterface;

class VisitorDelegate extends VisitorAbstract
{
    /**
     * Create a new VisitorDelegate.
     *
     * @param ContainerInterface $dependencyIContainer
     * @param ConfigManager      $configManager
     * @param string $visitorId             visitor unique identifier.
     * @param boolean $isAuthenticated
     * @param array              $context     visitor context. e.g: ["age"=>42, "isVip"=>true, "country"=>"UK"]
     * @param boolean $hasConsented
     * @param string|null $flagshipInstanceId
     * @param callable|null $onFetchFlagsStatusChanged
     */
    public function __construct(
        ContainerInterface $dependencyIContainer,
        ConfigManager $configManager,
        string $visitorId,
        bool $isAuthenticated = false,
        array $context = [],
        bool $hasConsented = false,
        string $flagshipInstanceId = null,
        callable $onFetchFlagsStatusChanged = null
    ) {
        parent::__construct();
        $this->onFetchFlagsStatusChanged = $onFetchFlagsStatusChanged;
        $this->setFlagshipInstanceId($flagshipInstanceId);
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
        $this->setFetchStatus(new FetchFlagsStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReason::VISITOR_CREATED));
    }

    /**
     * @return void
     */
    private function loadPredefinedContext()
    {
        $this->context[FlagshipContext::OS_NAME] = PHP_OS;
        $this->context[FlagshipConstant::FS_CLIENT] = FlagshipConstant::SDK_LANGUAGE;
        $this->context[FlagshipConstant::FS_VERSION] = FlagshipConstant::SDK_VERSION;
        $this->context[FlagshipConstant::FS_USERS] = $this->getVisitorId();
    }

    /**
     * @inheritDoc
     */
    public function updateContext(string $key, float|bool|int|string $value)
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
    public function authenticate(string $visitorId)
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
    public function visitorExposed($key, float|array|bool|int|string $defaultValue, FlagDTO $flag = null, bool $hasGetValueBeenCalled = false)
    {
        $this->getStrategy()->visitorExposed($key, $defaultValue, $flag, $hasGetValueBeenCalled);
    }


    /**
     * @inheritDoc
     */
    public function getFlagValue(string $key, float|array|bool|int|string $defaultValue, FlagDTO $flag = null, bool $userExposed = true)
    {
        return $this->getStrategy()->getFlagValue($key, $defaultValue, $flag, $userExposed);
    }


    /**
     * @inheritDoc
     */
    public function getFlagMetadata(string $key, FlagDTO $flag = null)
    {
        return $this->getStrategy()->getFlagMetadata($key, $flag);
    }

    /**
     * @inheritDoc
     */
    public function getFlag(string $key)
    {
        $fetchFlagsStatus = $this->getFetchStatus();
        if ($fetchFlagsStatus->getStatus() !== FSFetchStatus::FETCHED && $fetchFlagsStatus->getStatus() !== FSFetchStatus::FETCHING) {
            $this->logWarningSprintf(
                $this->getConfig(),
                FlagshipConstant::GET_FLAG,
                $this->flagSyncStatusMessage($fetchFlagsStatus->getReason()),
                [$this->getVisitorId(), $key]
            );
        }
        return new FSFlag($key, $this);
    }

    /**
     * @inheritDoc
     */
    public function getFlags()
    {
        return new FSFlagCollection($this);
    }

    /**
     * @param $status int
     * @return string
     */
    protected function flagSyncStatusMessage($status)
    {
        $message = "";
        $commonMessage = 'without calling `fetchFlags` method afterwards. So, the value of the flag `%s` might be outdated';
        switch ($status) {
            case FSFetchReason::VISITOR_CREATED:
                $message = "Visitor `%s` has been created without calling `fetchFlags` method afterwards. So, the value of the flag `%s` is the default value";
                break;
            case FSFetchReason::UPDATE_CONTEXT:
                $message = "Visitor context for visitor `%s` has been updated {$commonMessage}";
                break;
            case FSFetchReason::AUTHENTICATE:
                $message = "Visitor `%s` has been authenticated {$commonMessage}";
                break;
            case FSFetchReason::UNAUTHENTICATE:
                $message = "Visitor `%s` has been unauthenticated {$commonMessage}";
                break;
            case FSFetchReason::FETCH_ERROR:
                $message = "An error occurred while fetching flags for visitor `%s`. So, the value of the flag `%s` might be outdated";
                break;
            case FSFetchReason::READ_FROM_CACHE:
                $message = "Flags for visitor  `%s` have been fetched from cache";
                break;
        }
        return $message;
    }
}
