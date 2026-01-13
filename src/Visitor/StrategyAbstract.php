<?php

namespace Flagship\Visitor;

use DateTime;
use Exception;
use Flagship\Hit\UsageHit;
use Flagship\Enum\LogLevel;
use Flagship\Model\FlagDTO;
use Flagship\Traits\Helper;
use Flagship\Utils\MurmurHash;
use Flagship\Enum\FlagshipField;
use Flagship\Hit\Troubleshooting;
use Flagship\Utils\ConfigManager;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Model\VisitorCacheDTO;
use Flagship\Traits\ValidatorTrait;
use Flagship\Config\BucketingConfig;
use Flagship\Model\CampaignCacheDTO;
use Flagship\Model\ModificationsDTO;
use Flagship\Enum\VisitorCacheStatus;
use Flagship\Traits\HasSameTypeTrait;
use Flagship\Enum\TroubleshootingLabel;
use Flagship\Model\TroubleshootingData;
use Flagship\Model\VisitorCacheDataDTO;
use Flagship\Api\TrackingManagerAbstract;
use Flagship\Decision\DecisionManagerAbstract;
use Flagship\Cache\IVisitorCacheImplementation;
use Flagship\Model\CampaignDTO;

/**
 *
 */
abstract class StrategyAbstract implements VisitorCoreInterface, VisitorFlagInterface
{
    use ValidatorTrait;
    use HasSameTypeTrait;
    use Helper;

    public const DATA       = 'data';
    public const CAMPAIGNS  = 'campaigns';
    public const VISITOR_ID = 'visitorId';
    public const VISITOR_ID_MISMATCH_ERROR = "Visitor ID mismatch: '%s' vs '%s'";
    public const CAMPAIGN_ID               = 'campaignId';
    public const CAMPAIGN_TYPE             = 'type';
    public const VARIATION_GROUP_ID        = 'variationGroupId';
    public const VARIATION_ID              = 'variationId';
    public const LOOKUP_VISITOR_JSON_OBJECT_ERROR = 'JSON DATA must fit the type VisitorCache';
    public const VERSION             = 'version';
    public const CURRENT_VERSION     = 1;
    public const ASSIGNMENTS_HISTORY = 'assignmentsHistory';
    public const FLAGS               = 'flags';
    public const ACTIVATED           = 'activated';
    public const ANONYMOUS_ID        = 'anonymousId';
    public const CONSENT             = 'consent';
    public const CONTEXT             = 'context';

    /**
     * @var VisitorAbstract
     */
    protected VisitorAbstract $visitor;

    /**
     * @var MurmurHash
     */
    protected MurmurHash $murmurHash;

    protected ?string $flagshipInstanceId = null;


    /**
     * @param VisitorAbstract $visitor
     */
    public function __construct(VisitorAbstract $visitor)
    {
        $this->visitor = $visitor;
    }

    public function getFlagshipInstanceId(): ?string
    {
        return $this->flagshipInstanceId;
    }

    /**
     * @param ?string $flagshipInstanceId
     * @return StrategyAbstract
     */
    public function setFlagshipInstanceId(?string $flagshipInstanceId): self
    {
        $this->flagshipInstanceId = $flagshipInstanceId;
        return $this;
    }

    /**
     * @return MurmurHash
     */
    public function getMurmurHash(): MurmurHash
    {
        return $this->murmurHash;
    }

    /**
     * @param MurmurHash $murmurHash
     * @return StrategyAbstract
     */
    public function setMurmurHash(MurmurHash $murmurHash): self
    {
        $this->murmurHash = $murmurHash;
        return $this;
    }

    /**
     * @return VisitorAbstract
     */
    protected function getVisitor(): VisitorAbstract
    {
        return $this->visitor;
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager(): ConfigManager
    {
        return $this->getVisitor()->getConfigManager();
    }


    /**
     * @return FlagshipConfig
     */
    protected function getConfig(): FlagshipConfig
    {
        return $this->getVisitor()->getConfig();
    }


    /**
     * @return TrackingManagerAbstract
     */
    protected function getTrackingManager(): TrackingManagerAbstract
    {
        return $this->getConfigManager()->getTrackingManager();
    }


    /**
     * @return DecisionManagerAbstract
     */
    protected function getDecisionManager(): DecisionManagerAbstract
    {
        return $this->getConfigManager()->getDecisionManager();
    }

    abstract protected function updateContextKeyValue(string $key, float|bool|int|string $value): void;


    /**
     * @param  array<mixed> $item
     * @return boolean
     * @throws Exception
     */
    private function checkLookupVisitorDataV1(array $item): bool
    {
        if (!isset($item[self::DATA]) || !is_array($item[self::DATA])) {
            return false;
        }

        $data = $item[self::DATA];

        if (!isset($data[self::VISITOR_ID]) || !is_string($data[self::VISITOR_ID])) {
            return false;
        }

        $visitorId = $data[self::VISITOR_ID];

        $currentVisitorId = $this->getVisitor()->getVisitorId();
        $anonymousId = $this->getVisitor()->getAnonymousId();

        if ($visitorId !== $currentVisitorId && $visitorId !== $anonymousId) {
            throw new Exception(sprintf(
                self::VISITOR_ID_MISMATCH_ERROR,
                $visitorId,
                $currentVisitorId
            ));
        }

        if (!isset($data[self::CAMPAIGNS])) {
            return false;
        }

        $campaigns = $data[self::CAMPAIGNS];
        if (!is_array($campaigns)) {
            return false;
        }

        foreach ($campaigns as $campaign) {
            if (!is_array($campaign) || !$this->isValidCampaign($campaign)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<mixed> $campaign
     * @return bool
     */
    private function isValidCampaign(array $campaign): bool
    {
        return isset(
            $campaign[self::CAMPAIGN_ID],
            $campaign[self::CAMPAIGN_TYPE],
            $campaign[self::VARIATION_GROUP_ID],
            $campaign[self::VARIATION_ID]
        );
    }


    /**
     * @param  array<mixed> $item
     * @return boolean
     * @throws Exception
     */
    private function checkLookupVisitorData(array $item): bool
    {
        if (isset($item[self::VERSION]) && $item[self::VERSION] == 1) {
            return $this->checkLookupVisitorDataV1($item);
        }

        return false;
    }


    /**
     * @return void
     */
    public function lookupVisitor(): void
    {
        try {
            $visitorCacheInstance = $this->getConfig()->getVisitorCacheImplementation();
            if (!$visitorCacheInstance) {
                return;
            }

            $visitor = $this->getVisitor();

            $visitor->setVisitorCacheStatus(VisitorCacheStatus::NONE);

            $visitorCacheArray = $visitorCacheInstance->lookupVisitor($this->visitor->getVisitorId());

            $hasVisitorCache = !empty($visitorCacheArray);

            if ($hasVisitorCache) {
                $visitor->setVisitorCacheStatus(VisitorCacheStatus::VISITOR_ID_CACHE);
            }

            $anonymousId = $visitor->getAnonymousId();

            if (!$hasVisitorCache && $anonymousId) {
                $visitorCacheArray = $visitorCacheInstance->lookupVisitor($anonymousId);
                if (!empty($visitorCacheArray)) {
                    $visitor->setVisitorCacheStatus(VisitorCacheStatus::ANONYMOUS_ID_CACHE);
                }
            }

            if ($visitor->getVisitorCacheStatus() === VisitorCacheStatus::NONE || empty($visitorCacheArray)) {
                $visitor->visitorCache = null;
                return;
            }

            if (!$this->checkLookupVisitorData($visitorCacheArray)) {
                throw new Exception(self::LOOKUP_VISITOR_JSON_OBJECT_ERROR);
            }

            $visitor->visitorCache = VisitorCacheDTO::fromArray($visitorCacheArray);


            if ($visitor->getVisitorCacheStatus() === VisitorCacheStatus::VISITOR_ID_CACHE && $anonymousId) {
                $anonymousCache  = $visitorCacheInstance->lookupVisitor($anonymousId);
                if (!empty($anonymousCache)) {
                    $visitor->setVisitorCacheStatus(VisitorCacheStatus::VISITOR_ID_CACHE_WITH_ANONYMOUS_ID_CACHE);
                }
            }
        } catch (Exception $exception) {
            $this->logError($this->getConfig(), $exception->getMessage(), [FlagshipConstant::TAG => __FUNCTION__]);
        }
    }

    /**
     * Build campaigns cache from visitor campaigns
     * 
     * @param VisitorAbstract $visitor
     * @return array{campaigns: array<CampaignCacheDTO>, assignmentsHistory: array<string, string>}
     */
    private function buildCampaignsCache(VisitorAbstract $visitor): array
    {
        $assignmentsHistory = [];
        $campaigns = [];

        foreach ($visitor->campaigns as $campaign) {

            $variationGroupId = $campaign->getVariationGroupId();
            $variationId = $campaign->getVariation()->getId();

            $assignmentsHistory[$variationGroupId] = $variationId;

            $flags = new ModificationsDTO(
                $campaign->getVariation()->getModifications()->getType(),
                $campaign->getVariation()->getModifications()->getValue()
            );
            $campaignCache = new CampaignCacheDTO(
                $campaign->getId(),
                $variationGroupId,
                $variationId,
                $flags
            );

            $campaignCache->setSlug($campaign->getSlug())
                ->setType($campaign->getType())
                ->setActivated(false)
                ->setIsReference($campaign->getVariation()->getReference());

            $campaigns[] = $campaignCache;
        }

        return [
            'campaigns' => $campaigns,
            'assignmentsHistory' => $assignmentsHistory
        ];
    }

    /**
     * Merge new assignments history with existing one
     * 
     * @param array<string, string> $newAssignments
     * @param VisitorCacheDTO|null $existingCache
     * @return array<string, string>
     */
    private function mergeAssignmentsHistory(array $newAssignments, ?VisitorCacheDTO $existingCache): array
    {
        $existingAssignments = $existingCache?->getData()?->getAssignmentsHistory();

        if (empty($existingAssignments)) {
            return $newAssignments;
        }

        return [...$existingAssignments, ...$newAssignments];
    }

    /**
     * Create visitor cache DTO with all data
     * 
     * @param string $visitorId
     * @param string|null $anonymousId
     * @param VisitorAbstract $visitor
     * @param array<CampaignCacheDTO> $campaigns
     * @param array<string, string> $assignmentsHistory
     * @return VisitorCacheDTO
     */
    private function createVisitorCache(
        string $visitorId,
        ?string $anonymousId,
        VisitorAbstract $visitor,
        array $campaigns,
        array $assignmentsHistory
    ): VisitorCacheDTO {
        $data = new VisitorCacheDataDTO($visitorId, $anonymousId);

        $data
            ->setConsent($visitor->hasConsented())
            ->setContext($visitor->getContext())
            ->setCampaigns($campaigns)
            ->setAssignmentsHistory($assignmentsHistory);

        return new VisitorCacheDTO(self::CURRENT_VERSION, $data);
    }

    /**
     * Cache data for anonymous visitor
     * 
     * @param IVisitorCacheImplementation $cacheInstance
     * @param string $anonymousId
     * @param VisitorAbstract $visitor
     * @param array<CampaignCacheDTO> $campaigns
     * @param array<string, string> $assignmentsHistory
     * @return void
     */
    private function cacheAnonymousVisitor(
        IVisitorCacheImplementation $cacheInstance,
        string $anonymousId,
        VisitorAbstract $visitor,
        array $campaigns,
        array $assignmentsHistory
    ): void {
        $anonymousData = new VisitorCacheDataDTO($anonymousId, null);

        $anonymousData
            ->setConsent($visitor->hasConsented())
            ->setContext($visitor->getContext())
            ->setCampaigns($campaigns)
            ->setAssignmentsHistory($assignmentsHistory);

        $anonymousCache = new VisitorCacheDTO(self::CURRENT_VERSION, $anonymousData);

        $cacheInstance->cacheVisitor($anonymousId, $anonymousCache->toArray());
    }


    /**
     * Determine if anonymous visitor should be cached
     * 
     * @param VisitorAbstract $visitor
     * @return bool
     */
    private function shouldCacheAnonymousVisitor(VisitorAbstract $visitor): bool
    {
        $cacheStatus = $visitor->getVisitorCacheStatus();

        return $cacheStatus === VisitorCacheStatus::NONE
            || $cacheStatus === VisitorCacheStatus::VISITOR_ID_CACHE;
    }

    /**
     * @return void
     */
    public function cacheVisitor(): void
    {
        try {
            $visitorCacheInstance = $this->getConfig()->getVisitorCacheImplementation();
            if (!$visitorCacheInstance) {
                return;
            }

            $visitor = $this->getVisitor();
            $visitorId = $visitor->getVisitorId();
            $anonymousId = $visitor->getAnonymousId();

            $campaignsData = $this->buildCampaignsCache($visitor);

            $assignmentsHistory = $this->mergeAssignmentsHistory(
                $campaignsData['assignmentsHistory'],
                $visitor->visitorCache
            );

            $visitorCache = $this->createVisitorCache(
                $visitorId,
                $anonymousId,
                $visitor,
                $campaignsData['campaigns'],
                $assignmentsHistory
            );

            $visitorCacheInstance->cacheVisitor($visitorId, $visitorCache->toArray());

            if ($anonymousId &&  $this->shouldCacheAnonymousVisitor($visitor)) {
                $this->cacheAnonymousVisitor(
                    $visitorCacheInstance,
                    $anonymousId,
                    $visitor,
                    $campaignsData['campaigns'],
                    $assignmentsHistory
                );
            }

            $visitor->visitorCache = $visitorCache;
        } catch (Exception $exception) {
            $this->logError(
                $this->getConfig(),
                $exception->getMessage(),
                [FlagshipConstant::TAG => __FUNCTION__]
            );
        }
    }


    /**
     * @return void
     */
    public function flushVisitor(): void
    {
        try {
            $visitorCacheInstance = $this->getConfig()->getVisitorCacheImplementation();
            if (!$visitorCacheInstance) {
                return;
            }

            $visitorCacheInstance->flushVisitor($this->getVisitor()->getVisitorId());
        } catch (Exception $exception) {
            $this->logError($this->getConfig(), $exception->getMessage(), [FlagshipConstant::TAG => __FUNCTION__]);
        }
    }

    public function sendTroubleshootingHit(Troubleshooting $hit): void
    {
        $this->getTrackingManager()->addTroubleshootingHit($hit);
    }

    /**
     * @return DateTime
     */
    public function getCurrentDateTime(): DateTime
    {
        return new DateTime();
    }

    public function sendSdkConfigAnalyticHit(): void
    {
        if ($this->getConfig()->disableDeveloperUsageTracking()) {
            return;
        }
        $uniqueId = $this->getVisitor()->getVisitorId() . $this->getCurrentDateTime()->format("Y-m-d");
        $hash = $this->getMurmurHash()->murmurHash3Int32($uniqueId);
        $traffic = $hash % 1000;

        if ($traffic > FlagshipConstant::ANALYTIC_HIT_ALLOCATION) {
            return;
        }

        $visitor = $this->getVisitor();
        $config = $this->getConfig();
        $bucketingUrl = null;
        $fetchThirdPartyData = null;
        if ($config instanceof BucketingConfig) {
            $bucketingUrl = $config->getSyncAgentUrl();
            $fetchThirdPartyData = $config->getFetchThirdPartyData();
        }
        $analytic = new UsageHit();
        $analytic->setLabel(TroubleshootingLabel::SDK_CONFIG)
            ->setLogLevel(LogLevel::INFO)
            ->setSdkConfigMode($config->getDecisionMode())
            ->setSdkConfigLogLevel($config->getLogLevel())
            ->setSdkConfigTimeout($config->getTimeout())
            ->setSdkConfigTrackingManagerConfigStrategy($config->getCacheStrategy())
            ->setSdkConfigBucketingUrl($bucketingUrl)
            ->setSdkConfigFetchThirdPartyData($fetchThirdPartyData)
            ->setSdkConfigUsingOnVisitorExposed(!!$config->getOnVisitorExposed())
            ->setSdkConfigUsingCustomHitCache(!!$config->getHitCacheImplementation())
            ->setSdkConfigUsingCustomVisitorCache(!!$config->getVisitorCacheImplementation())
            ->setSdkStatus($visitor->getSdkStatus())
            ->setFlagshipInstanceId($this->getFlagshipInstanceId())
            ->setVisitorId($this->getFlagshipInstanceId() ?? "")
            ->setConfig($config);
        $this->getTrackingManager()->addUsageHit($analytic);
    }

    /**
     * /**
     * @param TroubleshootingData $troubleshootingData
     * @param FlagDTO[] $flagsDTO
     * @param CampaignDTO[] $campaigns
     * @param float|int $now
     * @return void
     * /
     * @return void
     */
    public function sendFetchFlagsTroubleshooting(
        TroubleshootingData $troubleshootingData,
        array $flagsDTO,
        array $campaigns,
        float|int $now
    ): void {
        $visitor = $this->getVisitor();
        $config = $this->getConfig();
        $bucketingUrl = null;
        $fetchThirdPartyData = null;
        if ($config instanceof BucketingConfig) {
            $bucketingUrl = $config->getSyncAgentUrl();
            $fetchThirdPartyData = $config->getFetchThirdPartyData();
        }

        $assignmentHistory = [];
        foreach ($flagsDTO as $item) {
            $assignmentHistory[$item->getVariationGroupId()] = $item->getVariationId();
        }
        $uniqueId = $visitor->getVisitorId() . $troubleshootingData->getEndDate()->getTimestamp();
        $hash = $this->getMurmurHash()->murmurHash3Int32($uniqueId);
        $traffic = $hash % 100;
        $visitor->setTraffic($traffic);

        $troubleshootingHit = new Troubleshooting();
        $troubleshootingHit->setLabel(TroubleshootingLabel::VISITOR_FETCH_CAMPAIGNS)
            ->setLogLevel(LogLevel::INFO)
            ->setVisitorSessionId($visitor->getInstanceId())
            ->setFlagshipInstanceId($visitor->getFlagshipInstanceId())
            ->setTraffic($traffic)
            ->setVisitorAssignmentHistory($assignmentHistory)
            ->setVisitorContext($visitor->getContext())
            ->setSdkStatus($visitor->getSdkStatus())
            ->setVisitorCampaigns($campaigns)
            ->setFlagshipInstanceId($this->getFlagshipInstanceId())
            ->setVisitorFlags($flagsDTO)
            ->setVisitorConsent($visitor->hasConsented())
            ->setVisitorIsAuthenticated(!!$visitor->getAnonymousId())
            ->setHttpResponseTime(($this->getNow() - $now))
            ->setSdkConfigMode($config->getDecisionMode())
            ->setSdkConfigLogLevel($config->getLogLevel())
            ->setSdkConfigTimeout($config->getTimeout())
            ->setSdkConfigBucketingUrl($bucketingUrl)
            ->setSdkConfigFetchThirdPartyData($fetchThirdPartyData)
            ->setSdkConfigUsingOnVisitorExposed(!!$config->getOnVisitorExposed())
            ->setSdkConfigUsingCustomHitCache(!!$config->getHitCacheImplementation())
            ->setSdkConfigUsingCustomVisitorCache(!!$config->getVisitorCacheImplementation())->setSdkConfigTrackingManagerConfigStrategy($config->getCacheStrategy())->setVisitorId($visitor->getVisitorId())
            ->setAnonymousId($visitor->getAnonymousId())->setConfig($config);

        $this->sendTroubleshootingHit($troubleshootingHit);
    }

    public function sendConsentHitTroubleshooting(): void
    {
        $consentHitTroubleshooting = $this->getVisitor()->getConsentHitTroubleshooting();
        if (!$consentHitTroubleshooting) {
            return;
        }
        $consentHitTroubleshooting->setTraffic($this->getVisitor()->getTraffic());
        $this->sendTroubleshootingHit($consentHitTroubleshooting);
        $this->getVisitor()->setConsentHitTroubleshooting(null);
    }

    /**
     * @param array<string, mixed> $context
     * @return void
     */
    public function initialContext(array $context): void
    {
        if (count($context) == 0) {
            return;
        }
        foreach ($context as $itemKey => $item) {
            if (!is_scalar($item) || empty($itemKey) || !is_string($itemKey)) {
                $this->logError(
                    $this->getVisitor()->getConfig(),
                    FlagshipConstant::CONTEXT_PARAM_ERROR,
                    [FlagshipConstant::TAG => FlagshipConstant::TAG_UPDATE_CONTEXT]
                );
                continue;
            }
            $this->updateContextKeyValue($itemKey, $item);
        }
    }
}
