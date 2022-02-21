<?php

namespace Flagship\Visitor;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;
use Flagship\Flag\Flag;
use Flagship\Flag\FlagInterface;
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

        $this->setConsent($hasConsented);

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

    public function fetchFlags()
    {
        $this->getStrategy()->fetchFlags();
    }

    /**
     * @inheritDoc
     */
    public function userExposed($key, $hasSameType, FlagDTO $flag = null)
    {
        $this->getStrategy()->userExposed($key, $hasSameType, $flag);
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

    protected function findFlagDTO($key)
    {
        foreach ($this->getFlagsDTO() as $flagDTO) {
            if ($flagDTO->getKey() === $key) {
                return $flagDTO;
            }
        }
        return null;
    }
    /**
     * @inheritDoc
     */
    public function getFlag($key, $defaultValue)
    {
        $flagDTO = $this->findFlagDTO($key);

        if ($flagDTO) {
            $metadata = new FlagMetadata(
                $flagDTO->getCampaignId(),
                $flagDTO->getVariationGroupId(),
                $flagDTO->getVariationId(),
                $flagDTO->getIsReference(),
                $flagDTO->getCampaignType()
            );
        } else {
            $metadata = new FlagMetadata("", "", "", false, "");
        }
        return new Flag($key, $this, $defaultValue, $metadata, $flagDTO);
    }
}
