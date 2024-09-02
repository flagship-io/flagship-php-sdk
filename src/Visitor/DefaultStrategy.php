<?php

namespace Flagship\Visitor;

use Flagship\Enum\FlagshipContext;
use Flagship\Hit\Event;
use Flagship\Hit\Activate;
use Flagship\Enum\LogLevel;
use Flagship\Model\FlagDTO;
use Flagship\Hit\HitAbstract;
use Flagship\Enum\DecisionMode;
use Flagship\Flag\FSFlagMetadata;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\FSFetchStatus;
use Flagship\Enum\FSFetchReason;
use Flagship\Hit\Troubleshooting;
use Flagship\Enum\FlagshipConstant;
use Flagship\Model\FetchFlagsStatus;
use Flagship\Enum\TroubleshootingLabel;

/**
 * DefaultStrategy
 */
class DefaultStrategy extends StrategyAbstract
{
    /**
     * @param boolean $hasConsented
     * @return void
     */
    public function setConsent(bool $hasConsented): void
    {
        if (!$hasConsented) {
            $this->flushVisitor();
        }

        $consentHit = new Event(EventCategory::USER_ENGAGEMENT, FlagshipConstant::FS_CONSENT);
        $consentHit->setLabel(
            FlagshipConstant::SDK_LANGUAGE . ':' . ($hasConsented ? 'true' : 'false')
        )->setConfig($this->getConfig())->setVisitorId($this->getVisitor()->getVisitorId())->setAnonymousId($this->getVisitor()->getAnonymousId());

        $trackingManager = $this->getTrackingManager();

        $trackingManager->addHit($consentHit);

        $visitor = $this->getVisitor();

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_SEND_HIT)->setLogLevel(LogLevel::INFO)->setTraffic($visitor->getTraffic())->setFlagshipInstanceId($visitor->getFlagshipInstanceId())->setVisitorSessionId($visitor->getInstanceId())->setHitContent($consentHit->toApiKeys())->setVisitorId($visitor->getVisitorId())->setConfig($this->getConfig())->setAnonymousId($visitor->getAnonymousId());

        if ($this->getDecisionManager() && $this->getDecisionManager()->getTroubleshootingData()) {
            $this->sendTroubleshootingHit($troubleshooting);
            return;
        }
        $visitor->setConsentHitTroubleshooting($troubleshooting);
    }

    /**
     * @param string $key  context key.
     * @param bool|string|numeric $value : context value.
     * @return void
     */
    protected function updateContextKeyValue(string $key, mixed $value): void
    {
        if (!$this->isKeyValid($key) || !$this->isValueValid($value)) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                FlagshipConstant::CONTEXT_PARAM_ERROR,
                [FlagshipConstant::TAG => FlagshipConstant::TAG_UPDATE_CONTEXT]
            );
            return;
        }

        if (
            $key === FlagshipContext::FLAGSHIP_CLIENT
            || $key === FlagshipContext::FLAGSHIP_VERSION || $key === FlagshipContext::FLAGSHIP_VISITOR
        ) {
            return;
        }

        $check = $this->checkFlagshipContext($key, $value, $this->visitor->getConfig());

        if ($check !== null && !$check) {
            return;
        }

        $this->getVisitor()->context[$key] = $value;
    }

    protected function fetchStatusUpdateContext(): void
    {
        $this->setFetchStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReason::UPDATE_CONTEXT);
    }

    /**
     * @param FSFetchStatus $status
     * @param FSFetchReason $reason
     * @return void
     */
    protected function setFetchStatus(FSFetchStatus $status, FSFetchReason $reason): void
    {
        $this->getVisitor()->setFetchStatus(new FetchFlagsStatus($status, $reason));
    }

    /**
     * @inheritDoc
     */
    public function updateContext(string $key, float|bool|int|string|null $value): void
    {
        $this->updateContextKeyValue($key, $value);
        $this->fetchStatusUpdateContext();
    }


    /**
     * @inheritDoc
     */
    public function updateContextCollection(array $context): void
    {
        $this->initialContext($context);
        $this->fetchStatusUpdateContext();
    }


    /**
     * @inheritDoc
     */
    public function clearContext(): void
    {
        $this->getVisitor()->context = [];
        $this->fetchStatusUpdateContext();
    }


    /**
     * @param string $functionName
     * @return void
     */
    private function logDeactivate(string $functionName): void
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
    public function authenticate(string $visitorId): void
    {
        if ($this->getVisitor()->getConfig()->getDecisionMode() == DecisionMode::BUCKETING) {
            $this->logDeactivate(__FUNCTION__);
            return;
        }

        $anonymousId = $this->getVisitor()->getAnonymousId();
        if (!empty($anonymousId)) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                FlagshipConstant::FLAGSHIP_VISITOR_ALREADY_AUTHENTICATE,
                [FlagshipConstant::TAG => __FUNCTION__]
            );
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
        $this->setFetchStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReason::AUTHENTICATE);

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_AUTHENTICATE)->setLogLevel(LogLevel::INFO)->setVisitorSessionId($this->getVisitor()->getInstanceId())->setVisitorContext($this->getVisitor()->getContext())->setTraffic($this->getVisitor()->getTraffic())->setFlagshipInstanceId($this->getFlagshipInstanceId())->setConfig($this->getConfig())->setVisitorId($this->getVisitor()->getVisitorId())->setAnonymousId($this->getVisitor()->getAnonymousId());

        $this->sendTroubleshootingHit($troubleshooting);
    }

    /**
     * @inheritDoc
     */
    public function unauthenticate(): void
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
        $this->setFetchStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReason::UNAUTHENTICATE);

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_UNAUTHENTICATE)->setLogLevel(LogLevel::INFO)->setVisitorContext($this->getVisitor()->getContext())->setVisitorSessionId($this->getVisitor()->getInstanceId())->setFlagshipInstanceId($this->getFlagshipInstanceId())->setTraffic($this->getVisitor()->getTraffic())->setConfig($this->getConfig())->setVisitorId($this->getVisitor()->getVisitorId())->setAnonymousId($this->getVisitor()->getAnonymousId());

        $this->sendTroubleshootingHit($troubleshooting);
    }

    /**
     * @param  VisitorAbstract $visitor
     * @return array
     */
    protected function fetchVisitorCampaigns(VisitorAbstract $visitor): array
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

    protected function logFetchFlagsStarted(string $functionName): void
    {
        $this->logDebugSprintf(
            $this->getConfig(),
            $functionName,
            FlagshipConstant::FETCH_FLAGS_STARTED,
            [$this->getVisitor()->getVisitorId()]
        );
    }

    protected function logFetchCampaignsSuccess($functionName, $campaigns, $now): void
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

    protected function logFetchFlagsFromCampaigns($functionName, $flagsDTO): void
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

    protected function sendTroubleshootingAndAnalyticHits($flagsDTO, $campaigns, $now): void
    {
        $troubleshootingData = $this->getDecisionManager()->getTroubleshootingData();
        $this->getTrackingManager()->setTroubleshootingData($troubleshootingData);

        if ($troubleshootingData) {
            $this->sendFetchFlagsTroubleshooting($troubleshootingData, $flagsDTO, $campaigns, $now);
            $this->sendConsentHitTroubleshooting();
        }

        $this->sendSdkConfigAnalyticHit();
    }


    protected function getCampaignsFromCacheIfNotArray($campaigns): array
    {
        if (!is_array($campaigns)) {
            $campaigns = $this->fetchVisitorCampaigns($this->getVisitor());
            if (count($campaigns)) {
                $this->setFetchStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReason::FLAGS_FETCHED_FROM_CACHE);
            }
        }

        return $campaigns;
    }



    /**
     * @inheritDoc
     */
    public function fetchFlags(): void
    {
        $functionName = __FUNCTION__;
        $decisionManager = $this->getDecisionManager();

        $this->logFetchFlagsStarted($functionName);

        $now = $this->getNow();

        $this->setFetchStatus(FSFetchStatus::FETCHING, FSFetchReason::NONE);

        $campaigns = $decisionManager->getCampaigns($this->getVisitor());

        if ($this->getDecisionManager()->getIsPanicMode()) {
            $this->setFetchStatus(FSFetchStatus::PANIC, FSFetchReason::NONE);
        }

        $this->logFetchCampaignsSuccess($functionName, $campaigns, $now);

        $campaigns = $this->getCampaignsFromCacheIfNotArray($campaigns);
        $this->getVisitor()->campaigns = $campaigns;

        $flagsDTO = $decisionManager->getFlagsData($campaigns);
        $this->getVisitor()->setFlagsDTO($flagsDTO);

        if ($this->getVisitor()->getFetchStatus()->getStatus() == FSFetchStatus::FETCHING) {
            $this->setFetchStatus(FSFetchStatus::FETCHED, FSFetchReason::NONE);
        }

        $this->logFetchFlagsFromCampaigns($functionName, $flagsDTO);

        $this->sendTroubleshootingAndAnalyticHits($flagsDTO, $campaigns, $now);
    }

    /**
     * @inheritDoc
     */
    public function sendHit(HitAbstract $hit): void
    {
        $trackingManager = $this->getTrackingManager();

        $visitor = $this->getVisitor();
        $hit->setConfig($visitor->getConfig())->setVisitorId($visitor->getVisitorId())->setAnonymousId($visitor->getAnonymousId())->setDs(FlagshipConstant::SDK_APP);

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
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_SEND_HIT)->setLogLevel(LogLevel::INFO)->setFlagshipInstanceId($visitor->getFlagshipInstanceId())->setVisitorSessionId($visitor->getInstanceId())->setHitContent($hit->toApiKeys())->setTraffic($visitor->getTraffic())->setVisitorId($visitor->getVisitorId())->setConfig($this->getConfig())->setAnonymousId($visitor->getAnonymousId());
        $this->sendTroubleshootingHit($troubleshooting);
    }

    /**
     * @param FlagDTO $flag
     * @param mixed|null $defaultValue
     * @return void
     */
    protected function activateFlag(FlagDTO $flag, mixed $defaultValue = null): void
    {
        $flagMetadata = new FSFlagMetadata(
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

        $activateHit->setFlagKey($flag->getKey())->setFlagValue($flag->getValue())->setFlagDefaultValue($defaultValue)->setVisitorContext($visitor->getContext())->setFlagMetadata($flagMetadata)->setVisitorId($visitor->getVisitorId())->setAnonymousId($visitor->getAnonymousId())->setConfig($this->getConfig());

        $this->getTrackingManager()->activateFlag($activateHit);

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel(TroubleshootingLabel::VISITOR_SEND_ACTIVATE)->setLogLevel(LogLevel::INFO)->setFlagshipInstanceId($this->getFlagshipInstanceId())->setVisitorSessionId($visitor->getInstanceId())->setHitContent($activateHit->toApiKeys())->setTraffic($this->getVisitor()->getTraffic())->setVisitorId($visitor->getVisitorId())->setAnonymousId($visitor->getAnonymousId())->setConfig($this->getConfig());
        $this->sendTroubleshootingHit($troubleshooting);
    }

    private function sendFlagTroubleshooting($label, $key, $defaultValue, $visitorExposed): void
    {
        $visitor = $this->getVisitor();
        $troubleshooting = new Troubleshooting();
        $troubleshooting->setLabel($label)->setLogLevel(LogLevel::WARNING)->setVisitorSessionId($visitor->getInstanceId())->setFlagshipInstanceId($this->getFlagshipInstanceId())->setTraffic($visitor->getTraffic())->setVisitorContext($visitor->getContext())->setFlagKey($key)->setFlagDefault($defaultValue)->setVisitorExposed($visitorExposed)->setVisitorId($visitor->getVisitorId())->setAnonymousId($visitor->getAnonymousId())->setConfig($this->getConfig());

        $this->sendTroubleshootingHit($troubleshooting);
    }

    /**
     * @param string $key
     * @param float|array|bool|int|string $defaultValue
     * @param FlagDTO|null $flag
     * @param bool $hasGetValueBeenCalled
     * @return void
     */
    public function visitorExposed(
        string $key,
        float|array|bool|int|string|null $defaultValue,
        FlagDTO $flag = null,
        bool $hasGetValueBeenCalled = false
    ): void {
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

            $this->sendFlagTroubleshooting(
                TroubleshootingLabel::VISITOR_EXPOSED_FLAG_NOT_FOUND,
                $key,
                $defaultValue,
                true
            );
            return;
        }

        if (!$hasGetValueBeenCalled) {
            $this->logInfoSprintf(
                $this->getConfig(),
                FlagshipConstant::FLAG_USER_EXPOSED,
                FlagshipConstant::VISITOR_EXPOSED_VALUE_NOT_CALLED,
                [
                 $this->getVisitor()->getVisitorId(),
                 $key,
                ]
            );

            $this->sendFlagTroubleshooting(
                TroubleshootingLabel::FLAG_VALUE_NOT_CALLED,
                $key,
                $defaultValue,
                true
            );
        }

        if (
            gettype($defaultValue) != "NULL"
            && gettype($flag->getValue()) != "NULL" && !$this->hasSameType($flag->getValue(), $defaultValue)
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

            $this->sendFlagTroubleshooting(
                TroubleshootingLabel::VISITOR_EXPOSED_TYPE_WARNING,
                $key,
                $defaultValue,
                true
            );
        }

        $this->activateFlag($flag, $defaultValue);
    }


    /**
     * @param string $key
     * @param float|array|bool|int|string $defaultValue
     * @param FlagDTO|null $flag
     * @param boolean $userExposed
     * @return float|array|bool|int|string|null
     */
    public function getFlagValue(
        string $key,
        float|array|bool|int|string|null $defaultValue,
        FlagDTO $flag = null,
        bool $userExposed = true
    ): float|array|bool|int|string|null {
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

            $this->sendFlagTroubleshooting(
                TroubleshootingLabel::GET_FLAG_VALUE_FLAG_NOT_FOUND,
                $key,
                $defaultValue,
                $userExposed
            );
            return $defaultValue;
        }

        if ($userExposed) {
            $this->activateFlag($flag, $defaultValue);
        }

        if (gettype($flag->getValue()) === "NULL") {
            return $defaultValue;
        }

        if (gettype($defaultValue) != "NULL" && !$this->hasSameType($flag->getValue(), $defaultValue)) {
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

            $this->sendFlagTroubleshooting(
                TroubleshootingLabel::GET_FLAG_VALUE_TYPE_WARNING,
                $key,
                $defaultValue,
                $userExposed
            );

            return $defaultValue;
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
     * @param string $key
     * @param  FlagDTO|null $flag
     * @return FSFlagMetadata
     */
    public function getFlagMetadata(string $key, FlagDTO $flag = null): FSFlagMetadata
    {
        $flagMetadataFuncName = 'flag.metadata';
        if (!$flag) {
            $this->logInfo(
                $this->getVisitor()->getConfig(),
                sprintf(FlagshipConstant::NO_FLAG_METADATA, $this->getVisitor()->getVisitorId(), $key),
                [FlagshipConstant::TAG => $flagMetadataFuncName]
            );
            return FSFlagMetadata::getEmpty();
        }

        return new FSFlagMetadata(
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
    }
}
