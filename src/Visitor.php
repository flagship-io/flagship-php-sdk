<?php

namespace Flagship;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Hit\HitAbstract;
use Flagship\Model\Modification;
use Flagship\Traits\LogTrait;
use Flagship\Traits\ValidatorTrait;
use JsonSerializable;

/**
 * Flagship visitor representation.
 *
 * @package Flagship
 */
class Visitor implements JsonSerializable
{
    use LogTrait;
    use ValidatorTrait;

    /**
     * @var FlagshipConfig
     */
    private $config;
    /**
     * @var string
     */
    private $visitorId;
    /**
     * @var array
     */
    private $context = [];

    /**
     * @var Modification[]
     */
    private $modifications = [];

    /**
     * Create a new visitor.
     *
     * @param FlagshipConfig $config
     * @param string         $visitorId : visitor unique identifier.
     * @param array          $context   : visitor context. e.g: ["age"=>42, "isVip"=>true, "country"=>"UK"]
     */
    public function __construct(FlagshipConfig $config, $visitorId, array $context = [])
    {
        $this->config = $config;
        $this->setVisitorId($visitorId);
        $this->updateContextCollection($context);
    }

    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->visitorId;
    }

    /**
     * @param  string $visitorId
     * @return Visitor
     */
    public function setVisitorId($visitorId)
    {
        if (empty($visitorId)) {
            $this->logError(
                $this->config,
                FlagshipConstant::VISITOR_ID_ERROR,
                [FlagshipConstant::TAG => __FUNCTION__]
            );
        } else {
            $this->visitorId = $visitorId;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Clear the current context and set a new context value
     *
     * @param  array $context : collection of keys, values. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
     * @return Visitor
     */
    public function setContext($context)
    {
        $this->context = [];
        $this->updateContextCollection($context);
        return $this;
    }


    /**
     * Update the visitor context values, matching the given keys, used for targeting.
     *
     * A new context value associated with this key will be created if there is no previous matching value.
     * Context key must be String, and value type must be one of the following : Number, Boolean, String.
     *
     * @param string                $key   : context key.
     * @param numeric|string|bool $value : context value.
     */
    public function updateContext($key, $value)
    {
        if (!$this->isKeyValid($key) || !$this->isValueValid($value)) {
            $this->logError(
                $this->config,
                FlagshipConstant::CONTEXT_PARAM_ERROR,
                [FlagshipConstant::TAG => FlagshipConstant::TAG_UPDATE_CONTEXT]
            );

            return;
        }
        $this->context[$key] = $value;
    }

    /**
     * Update the visitor context values, matching the given keys, used for targeting.
     *
     * A new context value associated with this key will be created if there is no previous matching value.
     * Context keys must be String, and values types must be one of the following : Number, Boolean, String.
     *
     * @param array $Context collection of keys, values. e.g: ["age"=>42, "IsVip"=>true, "country"=>"UK"]
     */
    public function updateContextCollection(array $Context)
    {
        foreach ($Context as $itemKey => $item) {
            $this->updateContext($itemKey, $item);
        }
    }

    /**
     * clear the actual visitor context
     */
    public function clearContext()
    {
        $this->context = [];
    }

    /**
     * @return array
     */
    public function getModifications()
    {
        return $this->modifications;
    }

    /**
     * @return FlagshipConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * This function return true if panic mode is enabled, otherwise return false
     *     Note: Return true if decisionManager is null
     * @see hasDecisionManager
     * @return bool
     */
    private function isOnPanicMode($functionName, $process)
    {
        if (!$this->hasDecisionManager($process)) {
            return true;
        }

        $check = $this->getConfig()->getDecisionManager()->getIsPanicMode();

        if ($check) {
            $this->logError(
                $this->config,
                sprintf(FlagshipConstant::PANIC_MODE_ERROR, $functionName),
                [FlagshipConstant::TAG => $process]
            );
        }
        return $check;
    }

    /**
     * Retrieve a modification value by its key. If no modification match the given
     * key or if the stored value type and default value type do not match, default value will be returned.
     *
     * @param string              $key          : key associated to the modification.
     * @param string|bool|numeric $defaultValue : default value to return.
     * @param bool                $activate     : Set this parameter to true to automatically
     *                                          report on our server that the
     *                                          current visitor has seen this modification. It is possible to call
     *                                          activateModification() later.

     * @return string|bool|numeric : modification value or default value.
     */
    public function getModification($key, $defaultValue, $activate = false)
    {
        if ($this->isOnPanicMode(__FUNCTION__, FlagshipConstant::TAG_GET_MODIFICATION)) {
            return $defaultValue;
        }

        if (!$this->isKeyValid($key)) {
            $this->logError(
                $this->config,
                sprintf(FlagshipConstant::GET_MODIFICATION_KEY_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]
            );
            return $defaultValue;
        }

        $modification = $this->getObjetModification($key);
        if (!$modification) {
            $this->logError(
                $this->config,
                sprintf(FlagshipConstant::GET_MODIFICATION_MISSING_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]
            );
            return $defaultValue;
        }

        if (gettype($modification->getValue()) !== gettype($defaultValue)) {
            $this->logError(
                $this->config,
                sprintf(FlagshipConstant::GET_MODIFICATION_CAST_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]
            );
            return $defaultValue;
        }

        if ($activate) {
            $this->activateModification($key);
        }
        return $modification->getValue();
    }

    /**
     * Get the campaign modification information value matching the given key.
     *
     * @param string  $key : key which identify the modification.
     * @return array|null
     */
    public function getModificationInfo($key)
    {
        if ($this->isOnPanicMode(__FUNCTION__, FlagshipConstant::TAG_GET_MODIFICATION_INFO)) {
            return null;
        }

        if (!$this->isKeyValid($key)) {
            $this->logError(
                $this->config,
                sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION_INFO]
            );
            return null;
        }

        $modification = $this->getObjetModification($key);

        if (!$modification) {
            $this->logError(
                $this->config,
                sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION_INFO]
            );
            return null;
        }

        return $this->parseToCampaign($modification);
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
     * Return the Modification that matches the key, otherwise return null
     *
     * @param  $key
     * @return Modification|null
     */
    private function getObjetModification($key)
    {
        foreach ($this->modifications as $modification) {
            if ($modification->getKey() === $key) {
                return $modification;
            }
        }
        return null;
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
        if (!$this->config->getDecisionManager()) {
            $this->logError(
                $this->config,
                FlagshipConstant::DECISION_MANAGER_MISSING_ERROR,
                [FlagshipConstant::TAG => $process]
            );
            return false;
        }
        return true;
    }

    /**
     * This function calls the decision api and update all the campaigns modifications
     * from the server according to the visitor context.
     * @return void
     */
    public function synchronizedModifications()
    {
        if (!$this->hasDecisionManager(FlagshipConstant::TAG_SYNCHRONIZED_MODIFICATION)) {
            return;
        }
        $campaigns = $this->config->getDecisionManager()->getCampaigns($this);
        $this->modifications = $this->config->getDecisionManager()->getModifications($campaigns);
    }

    /**
     * This function return true if trackingManager is not null,
     * otherwise log an error and return false
     *
     * @return bool
     */
    private function hasTrackingManager($process)
    {
        $check = $this->config->getTrackingManager();

        if (!$check) {
            $this->logError(
                $this->config,
                FlagshipConstant::TRACKER_MANAGER_MISSING_ERROR,
                [FlagshipConstant::TAG => $process]
            );
        }
        return !!$check;
    }


    /**
     * Report this user has seen this modification.
     *
     * @param $key : key which identify the modification to activate.
     * @return void
     */
    public function activateModification($key)
    {
        if ($this->isOnPanicMode(__FUNCTION__, FlagshipConstant::TAG_ACTIVE_MODIFICATION)) {
            return ;
        }

        $modification = $this->getObjetModification($key);
        if (!$modification) {
            $this->logError(
                $this->config,
                sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_ACTIVE_MODIFICATION]
            );
            return ;
        }

        if (!$this->hasTrackingManager(FlagshipConstant::TAG_ACTIVE_MODIFICATION)) {
            return ;
        }

        $this->config->getTrackingManager()->sendActive($this, $modification);
    }

    /**
     * Send a Hit to Flagship servers for reporting.
     * @param  HitAbstract $hit
     * @return void
     */
    public function sendHit(HitAbstract $hit)
    {
        if ($this->isOnPanicMode("sendHit", FlagshipConstant::TAG_SEND_HIT)) {
            return ;
        }

        if (!$this->hasTrackingManager(FlagshipConstant::TAG_SEND_HIT)) {
            return;
        }

        $hit->setConfig($this->config)
            ->setVisitorId($this->getVisitorId())
            ->setDs(FlagshipConstant::SDK_APP);

        if (!$hit->isReady()) {
            $this->logError(
                $this->config,
                $hit->getErrorMessage(),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_SEND_HIT]
            );
            return;
        }

        $this->config->getTrackingManager()->sendHit($hit);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'visitorId' => $this->getVisitorId(),
            'context' => $this->getContext(),
        ];
    }
}
