<?php

namespace Flagship\Visitor;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Flag\FlagMetadata;
use Flagship\Hit\HitAbstract;
use Flagship\Model\FlagDTO;
use Flagship\Traits\ValidatorTrait;

class DefaultStrategy extends VisitorStrategyAbstract
{
    /**
     * @inheritDoc
     */
    public function setConsent($hasConsented)
    {
        $trackingManager = $this->getTrackingManager(__FUNCTION__);
        if ($trackingManager) {
            $trackingManager->sendConsentHit($this->getVisitor(), $this->getConfig());
        }
    }

    /**
     * @inheritDoc
     */
    public function updateContext($key, $value)
    {
        if (!$this->isKeyValid($key) || !$this->isValueValid($value)) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                FlagshipConstant::CONTEXT_PARAM_ERROR,
                [FlagshipConstant::TAG => FlagshipConstant::TAG_UPDATE_CONTEXT]
            );
            return ;
        }

        if (preg_match("/^fs_/i", $key)) {
            return ;
        }

        $check = $this->checkFlagshipContext($key, $value, $this->visitor->getConfig());

        if ($check !== null && !$check) {
            return ;
        }

        $this->getVisitor()->context[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function updateContextCollection(array $context)
    {
        foreach ($context as $itemKey => $item) {
            $this->updateContext($itemKey, $item);
        }
    }

    /**
     * @inheritDoc
     */
    public function clearContext()
    {
        $this->getVisitor()->context = [];
    }

    private function logDeactivate($functionName)
    {
        $this->logError(
            $this->getVisitor()->getConfig(),
            sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_BUCKETING_ERROR,
                $functionName
            ),
            [FlagshipConstant::TAG => $functionName]
        );
    }

    /**
     * @inheritDoc
     */
    public function authenticate($visitorId)
    {
        if ($this->getVisitor()->getConfig()->getDecisionMode() == DecisionMode::BUCKETING) {
            $this->logDeactivate(__FUNCTION__);
            return;
        }
        if (empty($visitorId)) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                FlagshipConstant::VISITOR_ID_ERROR,
                [FlagshipConstant::TAG => __FUNCTION__]
            );
            return;
        }
        $this->getVisitor()->setAnonymousId($this->getVisitor()->getVisitorId());
        $this->getVisitor()->setVisitorId($visitorId);
    }

    /**
     * @inheritDoc
     */
    public function unauthenticate()
    {
        if ($this->getVisitor()->getConfig()->getDecisionMode() == DecisionMode::BUCKETING) {
            $this->logDeactivate(__FUNCTION__);
            return;
        }
        $anonymousId = $this->getVisitor()->getAnonymousId();
        if (!$anonymousId) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                FlagshipConstant::FLAGSHIP_VISITOR_NOT_AUTHENTIFICATE,
                [FlagshipConstant::TAG => __FUNCTION__]
            );
            return;
        }
        $this->getVisitor()->setVisitorId($anonymousId);
        $this->getVisitor()->setAnonymousId(null);
    }

    /**
     * Return the Modification that matches the key, otherwise return null
     *
     * @param  $key
     * @return FlagDTO|null
     */
    private function getObjetModification($key)
    {
        foreach ($this->getVisitor()->getModifications() as $modification) {
            if ($modification->getKey() === $key) {
                return $modification;
            }
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getModification($key, $defaultValue, $activate = false)
    {
        if (!$this->isKeyValid($key)) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_MODIFICATION_KEY_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]
            );
            return $defaultValue;
        }

        $modification = $this->getObjetModification($key);
        if (!$modification) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_MODIFICATION_MISSING_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]
            );
            return $defaultValue;
        }

        if (gettype($modification->getValue()) !== gettype($defaultValue)) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_MODIFICATION_CAST_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]
            );

            if (is_null($modification->getValue())) {
                $this->activateModification($key);
            }
            return $defaultValue;
        }

        if ($activate) {
            $this->activateModification($key);
        }
        return $modification->getValue();
    }

    /**
     * Build the Campaign of Modification
     *
     * @param  FlagDTO $modification Modification containing information
     * @return array JSON encoded string
     */
    private function parseToCampaign(FlagDTO $modification)
    {
        return [
            FlagshipField::FIELD_CAMPAIGN_ID => $modification->getCampaignId(),
            FlagshipField::FIELD_VARIATION_GROUP_ID => $modification->getVariationGroupId(),
            FlagshipField::FIELD_VARIATION_ID => $modification->getVariationId(),
            FlagshipField::FIELD_IS_REFERENCE => $modification->getIsReference(),
            FlagshipField::FIELD_VALUE => $modification->getValue()
        ];
    }

    /**
     * @inheritDoc
     */
    public function getModificationInfo($key)
    {
        if (!$this->isKeyValid($key)) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION_INFO]
            );
            return null;
        }

        $modification = $this->getObjetModification($key);

        if (!$modification) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION_INFO]
            );
            return null;
        }

        return $this->parseToCampaign($modification);
    }

    private function synchronizeFlags($functionName)
    {
        $decisionManager = $this->getDecisionManager($functionName);
        if (!$decisionManager) {
            return;
        }
        $flagsDTO = $decisionManager->getCampaignModifications($this->getVisitor());
        $this->getVisitor()->setFlagsDTO($flagsDTO);
    }

    /**
     * @inheritDoc
     */
    public function synchronizeModifications()
    {
        $this->synchronizeFlags(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function fetchFlags()
    {
        $this->synchronizeFlags(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function activateModification($key)
    {
        $modification = $this->getObjetModification($key);
        if (!$modification) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_ACTIVE_MODIFICATION]
            );
            return ;
        }
        $trackingManager =  $this->getTrackingManager(__FUNCTION__);
        if (!$trackingManager) {
            return ;
        }
        $trackingManager->sendActive($this->getVisitor(), $modification);
    }

    /**
     * @inheritDoc
     */
    public function sendHit(HitAbstract $hit)
    {
        $trackingManager =  $this->getTrackingManager(__FUNCTION__);

        if (!$trackingManager) {
            return;
        }

        $hit->setConfig($this->getVisitor()->getConfig())
            ->setVisitorId($this->getVisitor()->getVisitorId())
            ->setAnonymousId($this->getVisitor()->getAnonymousId())
            ->setDs(FlagshipConstant::SDK_APP);

        if (!$hit->isReady()) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                $hit->getErrorMessage(),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_SEND_HIT]
            );
            return;
        }

        $trackingManager->sendHit($hit);
    }

    /**
     * @inheritDoc
     */
    public function getModifications()
    {
        return $this->getVisitor()->getModifications();
    }

    public function userExposed($key, $hasSameType, FlagDTO $flag = null)
    {
        $functionName = 'userExposed';
        if (!$flag) {
            $this->logInfo(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_FLAG_ERROR, $key),
                [FlagshipConstant::TAG => $functionName]
            );
            return ;
        }
        if ($flag->getValue() && !$hasSameType) {
            $this->logInfo(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::USER_EXPOSED_CAST_ERROR, $key),
                [FlagshipConstant::TAG => $functionName]
            );
            return ;
        }
        $trackingManager =  $this->getTrackingManager(__FUNCTION__);
        $trackingManager->sendActive($this->getVisitor(), $flag);
    }

    public function getFlagValue($key, $defaultValue, FlagDTO $flag = null, $userExposed = true)
    {
        $functionName = 'getFlag value';
        if (!$flag) {
            $this->logInfo(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_FLAG_MISSING_ERROR, $key),
                [FlagshipConstant::TAG => $functionName]
            );
            return  $defaultValue;
        }

        if (!$this->hasSameType($flag->getValue(), $defaultValue)) {
            $this->logInfo(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_FLAG_CAST_ERROR, $key),
                [FlagshipConstant::TAG => $functionName]
            );
            if (!$flag->getValue() && $userExposed) {
                $this->userExposed($key, true, $flag);
            }
            return  $defaultValue;
        }
        if ($userExposed) {
            $this->userExposed($key, true, $flag);
        }
        return  $flag->getValue();
    }

    /**
     * @param string $key
     * @param FlagMetadata $metadata
     * @param bool $hasSameType
     * @return FlagMetadata
     */
    public function getFlagMetadata($key, FlagMetadata $metadata, $hasSameType)
    {
        $functionName = 'flag.metadata';
        if (!$hasSameType && $metadata->getCampaignId()) {
            $this->logInfo(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_METADATA_CAST_ERROR, $key),
                [FlagshipConstant::TAG => $functionName]
            );
            return  FlagMetadata::getEmpty();
        }
        return  $metadata;
    }
}
