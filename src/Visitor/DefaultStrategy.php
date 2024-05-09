<?php

namespace Flagship\Visitor;

use Flagship\Hit\Event;
use Flagship\Hit\Activate;
use Flagship\Enum\LogLevel;
use Flagship\Model\FlagDTO;
use Flagship\Hit\HitAbstract;
use Flagship\Enum\DecisionMode;
use Flagship\Flag\FlagMetadata;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\FSFetchStatus;
use Flagship\Enum\FlagSyncStatus;
use Flagship\Enum\FSFetchReasons;
use Flagship\Hit\Troubleshooting;
use Flagship\Enum\FlagshipConstant;
use Flagship\Model\FetchFlagsStatus;
use Flagship\Enum\TroubleshootingLabel;

/**
 * DefaultStrategy
 */
class DefaultStrategy extends StrategyAbstract
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

        $visitor = $this->getVisitor();

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_SEND_HIT)
            ->setLogLevel(LogLevel::INFO)
            ->setTraffic($visitor->getTraffic())
            ->setFlagshipInstanceId($visitor->getFlagshipInstanceId())
            ->setVisitorSessionId($visitor->getInstanceId())
            ->setHitContent($consentHit->toApiKeys())
            ->setVisitorId($visitor->getVisitorId())
            ->setConfig($this->getConfig())
            ->setAnonymousId($visitor->getAnonymousId());

        if ($this->getDecisionManager() && $this->getDecisionManager()->getTroubleshootingData()) {
            $this->sendTroubleshootingHit($troubleshooting);
            return;
        }
        $visitor->setConsentHitTroubleshooting($troubleshooting);
    }

    /**
     * @param string $key  context key.
     * @param numeric|string|bool $value : context value.
     * @return void
     */
    protected function updateContextKeyValue($key, $value)
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

    protected function fetchStatusUpdateContext()
    {
        $this->setFetchStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReasons::UPDATE_CONTEXT);
    }

    /**

     * @param int $status
     * @param int $reason
     * @return void
     */
    protected function setFetchStatus($status, $reason)
    {
        $this->getVisitor()->setFetchStatus(new FetchFlagsStatus($status, $reason));
    }

    /**
     * @inheritDoc
     */
    public function updateContext($key, $value)
    {
        $this->updateContextKeyValue($key, $value);
        $this->fetchStatusUpdateContext();
    }


    /**
     * @inheritDoc
     */
    public function updateContextCollection(array $context)
    {
        foreach ($context as $itemKey => $item) {
            $this->updateContextKeyValue($itemKey, $item);
        }
        $this->fetchStatusUpdateContext();
    }


    /**
     * @inheritDoc
     */
    public function clearContext()
    {
        $this->getVisitor()->context = [];
        $this->fetchStatusUpdateContext();
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
        $this->getVisitor()->setFlagSyncStatus(FlagSyncStatus::AUTHENTICATED);
        $this->setFetchStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReasons::AUTHENTICATE);

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_AUTHENTICATE)
            ->setLogLevel(LogLevel::INFO)
            ->setVisitorSessionId($this->getVisitor()->getInstanceId())
            ->setVisitorContext($this->getVisitor()->getContext())
            ->setTraffic($this->getVisitor()->getTraffic())
            ->setFlagshipInstanceId($this->getFlagshipInstanceId())
            ->setConfig($this->getConfig())
            ->setVisitorId($this->getVisitor()->getVisitorId())
            ->setAnonymousId($this->getVisitor()->getAnonymousId());

        $this->sendTroubleshootingHit($troubleshooting);
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
        $this->setFetchStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReasons::UNAUTHENTICATE);

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_UNAUTHENTICATE)
            ->setLogLevel(LogLevel::INFO)
            ->setVisitorContext($this->getVisitor()->getContext())
            ->setVisitorSessionId($this->getVisitor()->getInstanceId())
            ->setFlagshipInstanceId($this->getFlagshipInstanceId())
            ->setTraffic($this->getVisitor()->getTraffic())
            ->setConfig($this->getConfig())
            ->setVisitorId($this->getVisitor()->getVisitorId())
            ->setAnonymousId($this->getVisitor()->getAnonymousId());

        $this->sendTroubleshootingHit($troubleshooting);
    }

    /**
     * @param  VisitorAbstract $visitor
     * @return array
     */
    protected function fetchVisitorCampaigns(VisitorAbstract $visitor)
    {
        $now = $this->getNow();
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
                FlagshipField::FIELD_ID => $item[FlagshipField::FIELD_CAMPAIGN_ID],
                FlagshipField::FIELD_VARIATION_GROUP_ID => $item[FlagshipField::FIELD_VARIATION_GROUP_ID],
                FlagshipField::FIELD_VARIATION => [
                    FlagshipField::FIELD_ID => $item[self::CAMPAIGN_ID],
                    FlagshipField::FIELD_REFERENCE => $item[FlagshipField::FIELD_IS_REFERENCE],
                    FlagshipField::FIELD_MODIFICATIONS => [
                        FlagshipField::FIELD_CAMPAIGN_TYPE => $item[FlagshipField::FIELD_CAMPAIGN_TYPE],
                        FlagshipField::FIELD_VALUE => $item[self::FLAGS],
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

    protected function logFetchFlagsStarted($functionName)
    {
        $this->logDebugSprintf(
            $this->getConfig(),
            $functionName,
            FlagshipConstant::FETCH_FLAGS_STARTED,
            [$this->getVisitor()->getVisitorId()]
        );
    }

    protected function logFetchCampaignsSuccess($functionName, $campaigns, $now)
    {
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
    }

    protected function logFetchFlagsFromCampaigns($functionName, $campaigns, $flagsDTO)
    {
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
    }

    protected function sendTroubleshootingAndAnalyticHits($flagsDTO, $campaigns, $now)
    {
        $troubleshootingData = $this->getDecisionManager()->getTroubleshootingData();
        $this->getTrackingManager()->setTroubleshootingData($troubleshootingData);

        if ($troubleshootingData) {
            $this->sendFetchFlagsTroubleshooting($troubleshootingData, $flagsDTO, $campaigns, $now);
            $this->sendConsentHitTroubleshooting();
        }

        $this->sendSdkConfigAnalyticHit();
    }


    protected function getCampaignsFromCacheIfNotArray($campaigns)
    {
        if (!is_array($campaigns)) {
            $campaigns = $this->fetchVisitorCampaigns($this->getVisitor());
            if (count($campaigns)) {
                $this->setFetchStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReasons::READ_FROM_CACHE);
            }
        }

        return $campaigns;
    }



    /**
     * @inheritDoc
     */
    public function fetchFlags()
    {
        $functionName = __FUNCTION__;
        $decisionManager = $this->getDecisionManager($functionName);
        if (!$decisionManager) {
            return;
        }

        $this->logFetchFlagsStarted($functionName);

        $now = $this->getNow();

        $this->setFetchStatus(FSFetchStatus::FETCHING, FSFetchReasons::NONE);

        $campaigns = $decisionManager->getCampaigns($this->getVisitor());

        if ($this->getDecisionManager()->getIsPanicMode()) {
            $this->setFetchStatus(FSFetchStatus::PANIC, FSFetchReasons::NONE);
        }

        $this->logFetchCampaignsSuccess($functionName, $campaigns, $now);

        $campaigns = $this->getCampaignsFromCacheIfNotArray($campaigns);
        $this->getVisitor()->campaigns = $campaigns;

        $flagsDTO = $decisionManager->getFlagsData($campaigns);
        $this->getVisitor()->setFlagsDTO($flagsDTO);

        $this->getVisitor()->setFlagSyncStatus(FlagSyncStatus::FLAGS_FETCHED);

        if ($this->getVisitor()->getFetchStatus()->getStatus() == FSFetchStatus::FETCHING) {
            $this->setFetchStatus(FSFetchStatus::FETCHED, FSFetchReasons::NONE);
        }

        $this->logFetchFlagsFromCampaigns($functionName, $campaigns, $flagsDTO);

        $this->sendTroubleshootingAndAnalyticHits($flagsDTO, $campaigns, $now);
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
            ->setFlagshipInstanceId($visitor->getFlagshipInstanceId())
            ->setVisitorSessionId($visitor->getInstanceId())
            ->setHitContent($hit->toApiKeys())
            ->setTraffic($visitor->getTraffic())
            ->setVisitorId($visitor->getVisitorId())
            ->setConfig($this->getConfig())
            ->setAnonymousId($visitor->getAnonymousId());
        $this->sendTroubleshootingHit($troubleshooting);
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

        $activateHit
            ->setFlagKey($flag->getKey())
            ->setFlagValue($flag->getValue())
            ->setFlagDefaultValue($defaultValue)
            ->setVisitorContext($visitor->getContext())
            ->setFlagMetadata($flagMetadata)
            ->setVisitorId($visitor->getVisitorId())
            ->setAnonymousId($visitor->getAnonymousId())
            ->setConfig($this->getConfig());

        $this->getTrackingManager()->activateFlag($activateHit);

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_SEND_ACTIVATE)
            ->setLogLevel(LogLevel::INFO)
            ->setFlagshipInstanceId($this->getFlagshipInstanceId())
            ->setVisitorSessionId($visitor->getInstanceId())
            ->setHitContent($activateHit->toApiKeys())
            ->setTraffic($this->getVisitor()->getTraffic())
            ->setVisitorId($visitor->getVisitorId())
            ->setAnonymousId($visitor->getAnonymousId())
            ->setConfig($this->getConfig())
        ;
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
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setTraffic($visitor->getTraffic())
                ->setVisitorSessionId($visitor->getInstanceId())
                ->setVisitorContext($visitor->getContext())
                ->setFlagKey($key)
                ->setFlagDefault($defaultValue)
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId())

                ->setConfig($this->getConfig());
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
                ->setVisitorSessionId($visitor->getInstanceId())
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setTraffic($visitor->getTraffic())
                ->setVisitorContext($visitor->getContext())
                ->setFlagKey($key)
                ->setFlagDefault($defaultValue)
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId())
                ->setConfig($this->getConfig());
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
                ->setVisitorSessionId($visitor->getInstanceId())
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setTraffic($visitor->getTraffic())
                ->setVisitorContext($visitor->getContext())
                ->setFlagKey($key)
                ->setFlagDefault($defaultValue)
                ->setVisitorExposed($userExposed)
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId())
                ->setConfig($this->getConfig());

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
                ->setVisitorSessionId($visitor->getInstanceId())
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setTraffic($visitor->getTraffic())
                ->setVisitorContext($visitor->getContext())
                ->setFlagKey($key)
                ->setFlagDefault($defaultValue)
                ->setVisitorExposed($userExposed)
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId())
                ->setConfig($this->getConfig());

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
                ->setVisitorSessionId($visitor->getInstanceId())
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setTraffic($visitor->getTraffic())
                ->setVisitorContext($visitor->getContext())
                ->setFlagKey($key)
                ->setFlagMetadataCampaignId($metadata->getCampaignId())
                ->setFlagMetadataCampaignSlug($metadata->getSlug())
                ->setFlagMetadataCampaignType($metadata->getCampaignType())
                ->setFlagMetadataVariationGroupId($metadata->getVariationGroupId())
                ->setFlagMetadataVariationId($metadata->getVariationId())
                ->setFlagMetadataCampaignIsReference($metadata->isReference())
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId())
                ->setConfig($this->getConfig());

            $this->sendTroubleshootingHit($troubleshooting);
            return FlagMetadata::getEmpty();
        }

        return $metadata;
    }
}
