<?php

namespace Flagship\Visitor;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Hit\HitAbstract;
use Flagship\Model\Modification;
use Flagship\Traits\ValidatorTrait;

class DefaultStrategy extends VisitorStrategyAbstract
{
    use ValidatorTrait;

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
            return null;
        }

        $contextValue = $this->checkFlagshipContext($key, $value, $this->visitor->getConfig());

        if (!$contextValue) {
            return null;
        }
        if (is_array($contextValue)) {
            $key = $contextValue['key'];
            return $this->getVisitor()->context[$key] = $value;
        }
        return $this->getVisitor()->context[$key] = $value;
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

    /**
     * Return the Modification that matches the key, otherwise return null
     *
     * @param  $key
     * @return Modification|null
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
     * @param  Modification $modification Modification containing information
     * @return array JSON encoded string
     */
    private function parseToCampaign(Modification $modification)
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

    /**
     * This function return true if decisionManager is not null,
     * otherwise log an error and return false
     *
     * @param string $process : Process name
     * @return bool
     */
    private function hasDecisionManager($process)
    {
        if (!$this->getVisitor()->getConfigManager()->getDecisionManager()) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                FlagshipConstant::DECISION_MANAGER_MISSING_ERROR,
                [FlagshipConstant::TAG => $process]
            );
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function synchronizedModifications()
    {
        if (!$this->hasDecisionManager(FlagshipConstant::TAG_SYNCHRONIZED_MODIFICATION)) {
            return;
        }
        $modifications = $this->getVisitor()
            ->getConfigManager()
            ->getDecisionManager()
            ->getCampaignModifications($this->getVisitor());

        $this->getVisitor()->setModifications($modifications);
    }

    /**
     * This function return true if trackingManager is not null,
     * otherwise log an error and return false
     *
     * @return bool
     */
    private function hasTrackingManager($process)
    {
        $check = $this->getVisitor()->getConfigManager()->getTrackingManager();

        if (!$check) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                FlagshipConstant::TRACKER_MANAGER_MISSING_ERROR,
                [FlagshipConstant::TAG => $process]
            );
        }
        return (bool)$check;
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

        if (!$this->hasTrackingManager(FlagshipConstant::TAG_ACTIVE_MODIFICATION)) {
            return ;
        }

        $this->getVisitor()->getConfigManager()->getTrackingManager()->sendActive($this->getVisitor(), $modification);
    }

    /**
     * @inheritDoc
     */
    public function sendHit(HitAbstract $hit)
    {
        if (!$this->hasTrackingManager(FlagshipConstant::TAG_SEND_HIT)) {
            return;
        }

        $hit->setConfig($this->getVisitor()->getConfig())
            ->setVisitorId($this->getVisitor()->getVisitorId())
            ->setDs(FlagshipConstant::SDK_APP);

        if (!$hit->isReady()) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                $hit->getErrorMessage(),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_SEND_HIT]
            );
            return;
        }

        $this->getVisitor()->getConfigManager()->getTrackingManager()->sendHit($hit);
    }

    /**
     * @inheritDoc
     */
    public function getModifications()
    {
        return $this->getVisitor()->getModifications();
    }
}
