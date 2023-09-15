<?php

namespace Flagship\Visitor;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\FlagSyncStatus;
use Flagship\Enum\LogLevel;
use Flagship\Enum\TroubleshootingLabel;
use Flagship\Flag\FlagMetadata;
use Flagship\Hit\Activate;
use Flagship\Hit\Event;
use Flagship\Hit\HitAbstract;
use Flagship\Hit\Troubleshooting;
use Flagship\Model\FlagDTO;

/**
 * DefaultStrategy
 */
class DefaultStrategy extends VisitorStrategyAbstract
{
    const TYPE_NULL = 'NULL';


    /**
     * @param  boolean $hasConsented
     * @return void
     */
    public function setConsent($hasConsented)
    {
        if (!$hasConsented) {
            $this->flushVisitor();
        }

        $consentHit = new Event(EventCategory::USER_ENGAGEMENT, FlagshipConstant::FS_CONSENT);
        $consentHit->setLabel(
            FlagshipConstant::SDK_LANGUAGE . ':' . ($hasConsented ? 'true' : 'false')
        )->setConfig($this->getConfig())
            ->setVisitorId($this->getVisitor()->getVisitorId())
            ->setAnonymousId($this->getVisitor()->getAnonymousId());

        $trackingManager = $this->getTrackingManager();
        if (!$trackingManager) {
            return;
        }

        $trackingManager->addHit($consentHit);
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
            return;
        }

        if (preg_match('/^fs_/i', $key)) {
            return;
        }

        $check = $this->checkFlagshipContext($key, $value, $this->visitor->getConfig());

        if ($check !== null && !$check) {
            return;
        }

        $this->getVisitor()->context[$key] = $value;
        $this->getVisitor()->setFlagSyncStatus(FlagSyncStatus::CONTEXT_UPDATED);
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
     * @param  string $functionName
     * @return void
     */
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
     * @param  string $visitorId
     * @return void
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
        $this->getVisitor()->setFlagSyncStatus(FlagSyncStatus::AUTHENTICATED);

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_AUTHENTICATE)
            ->setLogLevel(LogLevel::INFO)
            ->setVisitorId($this->getVisitor()->getVisitorId())
            ->setAnonymousId($this->getVisitor()->getAnonymousId())
            ->setVisitorInstanceId($this->getVisitor()->getInstanceId())
            ->setVisitorContext($this->getVisitor()->getContext())
            ->setTraffic($this->getVisitor()->getTraffic())
            ->setFlagshipInstanceId($this->getFlagshipInstanceId())
            ->setConfig($this->getConfig());

        $this->sendTroubleshootingHit($troubleshooting);
    }

    /**
     * @return void
     */
    public function unauthenticate()
    {
        if ($this->getVisitor()->getConfig()->getDecisionMode() == DecisionMode::BUCKETING) {
            $this->logDeactivate(__FUNCTION__);
            return;
        }

        $anonymousId = $this->getVisitor()->getAnonymousId();
        if ($anonymousId === null) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                FlagshipConstant::FLAGSHIP_VISITOR_NOT_AUTHENTIFICATE,
                [FlagshipConstant::TAG => __FUNCTION__]
            );
            return;
        }

        $this->getVisitor()->setVisitorId($anonymousId);
        $this->getVisitor()->setAnonymousId(null);
        $this->getVisitor()->setFlagSyncStatus(FlagSyncStatus::UNAUTHENTICATED);

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_UNAUTHENTICATE)
            ->setLogLevel(LogLevel::INFO)
            ->setVisitorId($this->getVisitor()->getVisitorId())
            ->setAnonymousId($this->getVisitor()->getAnonymousId())
            ->setVisitorContext($this->getVisitor()->getContext())
            ->setVisitorInstanceId($this->getVisitor()->getInstanceId())
            ->setFlagshipInstanceId($this->getFlagshipInstanceId())
            ->setTraffic($this->getVisitor()->getTraffic())
            ->setConfig($this->getConfig());

        $this->sendTroubleshootingHit($troubleshooting);
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
     * @param  string  $key
     * @param  mixed   $defaultValue
     * @param  boolean $activate
     * @return boolean|float|integer|string
     */
    public function getModification($key, $defaultValue, $activate = false)
    {
        if ($this->isKeyValid($key) === false) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_MODIFICATION_KEY_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]
            );
            return $defaultValue;
        }

        $modification = $this->getObjetModification($key);
        if (!$modification) {
            $this->logInfo(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_MODIFICATION_MISSING_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]
            );
            return $defaultValue;
        }

        if (gettype($modification->getValue()) !== gettype($defaultValue)) {
            $this->logInfo(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_MODIFICATION_CAST_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]
            );

            if (is_null($modification->getValue())) {
                $this->activateModification($key);
            }

            return $defaultValue;
        }

        if ($activate === true) {
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
            FlagshipField::FIELD_CAMPAIGN_ID        => $modification->getCampaignId(),
            FlagshipField::FIELD_VARIATION_GROUP_ID => $modification->getVariationGroupId(),
            FlagshipField::FIELD_VARIATION_ID       => $modification->getVariationId(),
            FlagshipField::FIELD_IS_REFERENCE       => $modification->getIsReference(),
            FlagshipField::FIELD_VALUE              => $modification->getValue(),
        ];
    }


    /**
     * @param  string $key
     * @return array|null
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
     * @param  VisitorAbstract $visitor
     * @return array
     */
    protected function fetchVisitorCampaigns(VisitorAbstract $visitor)
    {
        $now          = $this->getNow();
        $visitorCache = $visitor->visitorCache;
        if (
            !isset(
                $visitorCache,
                $visitorCache[self::DATA],
                $visitorCache[self::DATA][self::CAMPAIGNS]
            ) ||
            !is_array($visitorCache[self::DATA][self::CAMPAIGNS])
        ) {
            return [];
        }

        $data = $visitorCache[self::DATA];
        $visitor->updateContextCollection($data[self::CONTEXT]);
        $campaigns = [];
        foreach ($data[self::CAMPAIGNS] as $item) {
            $campaigns[] = [
                FlagshipField::FIELD_ID                 => $item[FlagshipField::FIELD_CAMPAIGN_ID],
                FlagshipField::FIELD_VARIATION_GROUP_ID => $item[FlagshipField::FIELD_VARIATION_GROUP_ID],
                FlagshipField::FIELD_VARIATION          => [
                    FlagshipField::FIELD_ID            => $item[self::CAMPAIGN_ID],
                    FlagshipField::FIELD_REFERENCE     => $item[FlagshipField::FIELD_IS_REFERENCE],
                    FlagshipField::FIELD_MODIFICATIONS => [
                        FlagshipField::FIELD_CAMPAIGN_TYPE => $item[FlagshipField::FIELD_CAMPAIGN_TYPE],
                        FlagshipField::FIELD_VALUE         => $item[self::FLAGS],
                    ],
                ],
            ];
        }

        if (count($campaigns)) {
            $this->logDebugSprintf(
                $this->getConfig(),
                FlagshipConstant::PROCESS_FETCHING_FLAGS,
                FlagshipConstant::FETCH_CAMPAIGNS_FROM_CACHE,
                [
                    $this->getVisitor()->getVisitorId(),
                    $this->getVisitor()->getAnonymousId(),
                    $this->getVisitor()->getContext(),
                    $campaigns,
                    ($this->getNow() - $now),
                ]
            );
        }

        return $campaigns;
    }

    /**
     * @param  string $functionName
     * @return void
     */
    private function globalFetchFlags($functionName)
    {
        $decisionManager = $this->getDecisionManager($functionName);
        if (!$decisionManager) {
            return;
        }

        $this->logDebugSprintf(
            $this->getConfig(),
            $functionName,
            FlagshipConstant::FETCH_FLAGS_STARTED,
            [$this->getVisitor()->getVisitorId()]
        );

        $now = $this->getNow();

        $campaigns = $decisionManager->getCampaigns($this->getVisitor());

        $troubleshootingData = $this->getDecisionManager()->getTroubleshootingData();
        $this->getTrackingManager()->setTroubleshootingData($troubleshootingData);

        $this->logDebugSprintf(
            $this->getConfig(),
            $functionName,
            FlagshipConstant::FETCH_CAMPAIGNS_SUCCESS,
            [
                $this->getVisitor()->getVisitorId(),
                $this->getVisitor()->getAnonymousId(),
                $this->getVisitor()->getContext(),
                $campaigns,
                ($this->getNow() - $now),
            ]
        );

        if (!is_array($campaigns)) {
            $campaigns = $this->fetchVisitorCampaigns($this->getVisitor());
        }

        $this->getVisitor()->campaigns = $campaigns;
        $flagsDTO = $decisionManager->getModifications($campaigns);
        $this->getVisitor()->setFlagsDTO($flagsDTO);

        $this->getVisitor()->setFlagSyncStatus(FlagSyncStatus::FLAGS_FETCHED);

        $this->logDebugSprintf(
            $this->getConfig(),
            $functionName,
            FlagshipConstant::FETCH_FLAGS_FROM_CAMPAIGNS,
            [
                $this->getVisitor()->getVisitorId(),
                $this->getVisitor()->getAnonymousId(),
                $this->getVisitor()->getContext(),
                $flagsDTO,
            ]
        );


        if ($troubleshootingData) {
            $assignmentHistory = [];
            foreach ($flagsDTO as $item) {
                $assignmentHistory[$item->getVariationGroupId()] = $item->getVariationId();
            }
            $visitor = $this->getVisitor();
            $uniqueId = $visitor->getVisitorId() . $troubleshootingData->getEndDate()->getTimestamp();
            $hash = $this->getMurmurHash()->murmurHash3Int32($uniqueId);
            $traffic = $hash % 100;
            $visitor->setTraffic($traffic);

            $troubleshootingHit = new Troubleshooting();
            $troubleshootingHit->setLabel(TroubleshootingLabel::VISITOR_FETCH_CAMPAIGNS)
                ->setLogLevel(LogLevel::INFO)
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId())
                ->setVisitorInstanceId($visitor->getInstanceId())
                ->setTraffic($traffic)
                ->setConfig($this->getConfig())
                ->setVisitorAssignmentHistory($assignmentHistory)
                ->setVisitorContext($visitor->getContext())
                ->setSdkStatus($visitor->getSdkStatus())
                ->setVisitorCampaigns($campaigns)
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setVisitorFlags($flagsDTO)
                ->setVisitorConsent($visitor->hasConsented())
                ->setVisitorIsAuthenticated(!!$visitor->getAnonymousId())
                ->setHttpResponseTime(($this->getNow() - $now))
                ->setSdkConfigMode($this->getConfig()->getDecisionMode())
                ->setSdkConfigTimeout($this->getConfig()->getTimeout())
                ->setSdkConfigTrackingManagerConfigStrategy($this->getConfig()->getCacheStrategy());

            $this->sendTroubleshootingHit($troubleshootingHit);
        }
    }


    /**
     * @return void
     */
    public function synchronizeModifications()
    {
        $this->globalFetchFlags(__FUNCTION__);
    }


    /**
     * @inheritDoc
     */
    public function fetchFlags()
    {
        $this->globalFetchFlags(__FUNCTION__);
    }


    /**
     * @param  string $key
     * @return void
     */
    public function activateModification($key)
    {
        $modification = $this->getObjetModification($key);
        if ($modification === null) {
            $this->logInfo(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_ACTIVE_MODIFICATION]
            );
            return;
        }
        $this->activateFlag($modification);
    }


    /**
     * @inheritDoc
     */
    public function sendHit(HitAbstract $hit)
    {
        $trackingManager = $this->getTrackingManager(__FUNCTION__);

        if ($trackingManager === null) {
            return;
        }

        $visitor = $this->getVisitor();
        $hit->setConfig($visitor->getConfig())
            ->setVisitorId($visitor->getVisitorId())
            ->setAnonymousId($visitor->getAnonymousId())
            ->setDs(FlagshipConstant::SDK_APP);

        if ($hit->isReady() === false) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                $hit->getErrorMessage(),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_SEND_HIT]
            );
            return;
        }

        $trackingManager->addHit($hit);

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_SEND_HIT)
            ->setLogLevel(LogLevel::INFO)
            ->setTraffic($visitor->getTraffic())
            ->setVisitorId($visitor->getVisitorId())
            ->setVisitorInstanceId($visitor->getInstanceId())
            ->setFlagshipInstanceId($this->getFlagshipInstanceId())
            ->setAnonymousId($visitor->getAnonymousId())
            ->setConfig($this->getConfig())
            ->setHitContent($hit->toApiKeys());
        $this->sendTroubleshootingHit($troubleshooting);
    }


    /**
     * @return array|FlagDTO[]
     */
    public function getModifications()
    {
        return $this->getVisitor()->getModifications();
    }

    /**
     * @param FlagDTO $flag
     * @param mixed $defaultValue
     * @return void
     */
    protected function activateFlag(FlagDTO $flag, $defaultValue = null)
    {
        $flagMetadata = new FlagMetadata(
            $flag->getCampaignId(),
            $flag->getVariationGroupId(),
            $flag->getVariationId(),
            $flag->getIsReference(),
            $flag->getCampaignType(),
            $flag->getSlug(),
            $flag->getCampaignName(),
            $flag->getVariationGroupName(),
            $flag->getVariationName()
        );

        $visitor = $this->getVisitor();
        $activateHit = new Activate($flag->getVariationGroupId(), $flag->getVariationId());

        $activateHit->setConfig($this->getConfig())
            ->setVisitorId($visitor->getVisitorId())
            ->setAnonymousId($visitor->getAnonymousId())
            ->setFlagKey($flag->getKey())
            ->setFlagValue($flag->getValue())
            ->setFlagDefaultValue($defaultValue)
            ->setVisitorContext($visitor->getContext())
            ->setFlagMetadata($flagMetadata);

        $this->getTrackingManager()->activateFlag($activateHit);

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_SEND_ACTIVATE)
            ->setLogLevel(LogLevel::INFO)
            ->setTraffic($this->getVisitor()->getTraffic())
            ->setVisitorId($visitor->getVisitorId())
            ->setAnonymousId($visitor->getAnonymousId())
            ->setVisitorInstanceId($visitor->getInstanceId())
            ->setFlagshipInstanceId($this->getFlagshipInstanceId())
            ->setConfig($this->getConfig())
            ->setHitContent($activateHit->toApiKeys());
        $this->sendTroubleshootingHit($troubleshooting);
    }

    /**
     * @param  string       $key
     * @param  mixed        $defaultValue
     * @param  FlagDTO|null $flag
     * @return void
     */
    public function visitorExposed($key, $defaultValue, FlagDTO $flag = null)
    {
        if (!$flag) {
            $this->logInfoSprintf(
                $this->getConfig(),
                FlagshipConstant::FLAG_USER_EXPOSED,
                FlagshipConstant::USER_EXPOSED_NO_FLAG_ERROR,
                [
                    $this->getVisitor()->getVisitorId(),
                    $key,
                ]
            );

            $visitor = $this->getVisitor();
            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_EXPOSED_FLAG_NOT_FOUND)
                ->setLogLevel(LogLevel::WARNING)
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId())
                ->setVisitorInstanceId($visitor->getInstanceId())
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setTraffic($visitor->getTraffic())
                ->setConfig($this->getConfig())
                ->setVisitorContext($visitor->getContext())
                ->setFlagKey($key)
                ->setFlagDefault($defaultValue);
            $this->sendTroubleshootingHit($troubleshooting);
            return;
        }

        if (
            gettype($defaultValue) != self::TYPE_NULL
            && gettype($flag->getValue()) != self::TYPE_NULL && !$this->hasSameType($flag->getValue(), $defaultValue)
        ) {
            $this->logInfoSprintf(
                $this->getConfig(),
                FlagshipConstant::FLAG_USER_EXPOSED,
                FlagshipConstant::USER_EXPOSED_CAST_ERROR,
                [
                    $this->getVisitor()->getVisitorId(),
                    $key,
                ]
            );

            $visitor = $this->getVisitor();
            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_EXPOSED_TYPE_WARNING)
                ->setLogLevel(LogLevel::WARNING)
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId())
                ->setVisitorInstanceId($visitor->getInstanceId())
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setTraffic($visitor->getTraffic())
                ->setConfig($this->getConfig())
                ->setVisitorContext($visitor->getContext())
                ->setFlagKey($key)
                ->setFlagDefault($defaultValue);
            $this->sendTroubleshootingHit($troubleshooting);
            return;
        }

        $this->activateFlag($flag, $defaultValue);
    }


    /**
     * @param  string       $key
     * @param  mixed        $defaultValue
     * @param  FlagDTO|null $flag
     * @param  boolean      $userExposed
     * @return array|boolean|float|integer|string
     */
    public function getFlagValue($key, $defaultValue, FlagDTO $flag = null, $userExposed = true)
    {
        $visitor = $this->getVisitor();
        if (!$flag) {
            $this->logInfoSprintf(
                $this->getConfig(),
                FlagshipConstant::FLAG_VALUE,
                FlagshipConstant::GET_FLAG_MISSING_ERROR,
                [
                    $this->getVisitor()->getVisitorId(),
                    $key,
                    $defaultValue,
                ]
            );

            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::GET_FLAG_VALUE_FLAG_NOT_FOUND)
                ->setLogLevel(LogLevel::WARNING)
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId())
                ->setVisitorInstanceId($visitor->getInstanceId())
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setTraffic($visitor->getTraffic())
                ->setConfig($this->getConfig())
                ->setVisitorContext($visitor->getContext())
                ->setFlagKey($key)
                ->setFlagDefault($defaultValue)
                ->setVisitorExposed($userExposed);

            $this->sendTroubleshootingHit($troubleshooting);
            return $defaultValue;
        }

        if (gettype($flag->getValue()) === self::TYPE_NULL) {
            if ($userExposed) {
                $this->visitorExposed($key, $defaultValue, $flag);
            }
            return $defaultValue;
        }

        if (gettype($defaultValue) != self::TYPE_NULL && !$this->hasSameType($flag->getValue(), $defaultValue)) {
            $this->logInfoSprintf(
                $this->getConfig(),
                FlagshipConstant::FLAG_VALUE,
                FlagshipConstant::GET_FLAG_CAST_ERROR,
                [
                    $this->getVisitor()->getVisitorId(),
                    $key,
                    $defaultValue,
                ]
            );

            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::GET_FLAG_VALUE_TYPE_WARNING)
                ->setLogLevel(LogLevel::WARNING)
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId())
                ->setVisitorInstanceId($visitor->getInstanceId())
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setTraffic($visitor->getTraffic())
                ->setConfig($this->getConfig())
                ->setVisitorContext($visitor->getContext())
                ->setFlagKey($key)
                ->setFlagDefault($defaultValue)
                ->setVisitorExposed($userExposed);

            $this->sendTroubleshootingHit($troubleshooting);
            return $defaultValue;
        }

        if ($userExposed) {
            $this->visitorExposed($key, $defaultValue, $flag);
        }

        $this->logDebugSprintf(
            $this->getConfig(),
            FlagshipConstant::FLAG_VALUE,
            FlagshipConstant::GET_FLAG_VALUE,
            [
                $this->getVisitor()->getVisitorId(),
                $key,
                $flag->getValue(),
            ]
        );

        return $flag->getValue();
    }


    /**
     * @param  string       $key
     * @param  FlagMetadata $metadata
     * @param  boolean      $hasSameType
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
            $visitor = $this->getVisitor();
            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::GET_FLAG_METADATA_TYPE_WARNING)
                ->setLogLevel(LogLevel::WARNING)
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId())
                ->setVisitorInstanceId($visitor->getInstanceId())
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setTraffic($visitor->getTraffic())
                ->setConfig($this->getConfig())
                ->setVisitorContext($visitor->getContext())
                ->setFlagKey($key)
                ->setFlagMetadataCampaignId($metadata->getCampaignId())
                ->setFlagMetadataCampaignSlug($metadata->getSlug())
                ->setFlagMetadataCampaignType($metadata->getCampaignType())
                ->setFlagMetadataVariationGroupId($metadata->getVariationGroupId())
                ->setFlagMetadataVariationId($metadata->getVariationId())
                ->setFlagMetadataCampaignIsReference($metadata->isReference());

            $this->sendTroubleshootingHit($troubleshooting);
            return FlagMetadata::getEmpty();
        }

        return $metadata;
    }
}
