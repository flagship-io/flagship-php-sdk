<?php

namespace Flagship\Visitor;

use DateTime;
use Exception;
use Flagship\Api\TrackingManagerAbstract;
use Flagship\Config\BucketingConfig;
use Flagship\Config\FlagshipConfig;
use Flagship\Decision\DecisionManagerAbstract;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\LogLevel;
use Flagship\Enum\TroubleshootingLabel;
use Flagship\Hit\UsageHit;
use Flagship\Hit\Troubleshooting;
use Flagship\Model\FlagDTO;
use Flagship\Model\TroubleshootingData;
use Flagship\Traits\HasSameTypeTrait;
use Flagship\Traits\Helper;
use Flagship\Traits\ValidatorTrait;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\MurmurHash;

/**
 *
 */
abstract class StrategyAbstract implements VisitorCoreInterface, VisitorFlagInterface
{
    use ValidatorTrait;
    use HasSameTypeTrait;
    use Helper;

    const DATA       = 'data';
    const CAMPAIGNS  = 'campaigns';
    const VISITOR_ID = 'visitorId';
    const VISITOR_ID_MISMATCH_ERROR = "Visitor ID mismatch: '%s' vs '%s'";
    const CAMPAIGN_ID               = 'campaignId';
    const CAMPAIGN_TYPE             = 'type';
    const VARIATION_GROUP_ID        = 'variationGroupId';
    const VARIATION_ID              = 'variationId';
    const LOOKUP_VISITOR_JSON_OBJECT_ERROR = 'JSON DATA must fit the type VisitorCache';
    const VERSION             = 'version';
    const CURRENT_VERSION     = 1;
    const ASSIGNMENTS_HISTORY = 'assignmentsHistory';
    const FLAGS               = 'flags';
    const ACTIVATED           = 'activated';
    const ANONYMOUS_ID        = 'anonymousId';
    const CONSENT             = 'consent';
    const CONTEXT             = 'context';

    /**
     * @var VisitorAbstract
     */
    protected $visitor;

    /**
     * @var MurmurHash
     */
    protected $murmurHash;

    protected $flagshipInstanceId;


    /**
     * @param VisitorAbstract $visitor
     */
    public function __construct(VisitorAbstract $visitor)
    {
        $this->visitor = $visitor;
    }


    /**
     * @return mixed
     */
    public function getFlagshipInstanceId()
    {
        return $this->flagshipInstanceId;
    }

    /**
     * @param mixed $flagshipInstanceId
     * @return StrategyAbstract
     */
    public function setFlagshipInstanceId($flagshipInstanceId)
    {
        $this->flagshipInstanceId = $flagshipInstanceId;
        return $this;
    }

    /**
     * @return MurmurHash
     */
    public function getMurmurHash()
    {
        return $this->murmurHash;
    }

    /**
     * @param MurmurHash $murmurHash
     * @return StrategyAbstract
     */
    public function setMurmurHash($murmurHash)
    {
        $this->murmurHash = $murmurHash;
        return $this;
    }

    /**
     * @return VisitorAbstract
     */
    protected function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager()
    {
        return $this->getVisitor()->getConfigManager();
    }


    /**
     * @return FlagshipConfig
     */
    protected function getConfig()
    {
        return $this->getVisitor()->getConfig();
    }



    /**
     * @param  string $process
     * @return TrackingManagerAbstract|null
     */
    protected function getTrackingManager($process = null)
    {
        $trackingManager = $this->getConfigManager()->getTrackingManager();

        if (!$trackingManager) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                FlagshipConstant::TRACKER_MANAGER_MISSING_ERROR,
                [FlagshipConstant::TAG => $process]
            );
        }

        return $trackingManager;
    }


    /**
     * @param  string $process
     * @return DecisionManagerAbstract|null
     */
    protected function getDecisionManager($process = null)
    {
        $decisionManager = $this->getConfigManager()->getDecisionManager();
        if (!$decisionManager) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                FlagshipConstant::DECISION_MANAGER_MISSING_ERROR,
                [FlagshipConstant::TAG => $process]
            );
        }

        return $decisionManager;
    }


    /**
     * @throws Exception
     */
    private function checkLookupVisitorDataV1(array $item)
    {
        if (!$item || !isset($item[self::DATA]) || !isset($item[self::DATA][self::VISITOR_ID])) {
            return false;
        }

        $data      = $item[self::DATA];
        $visitorId = $data[self::VISITOR_ID];

        if ($visitorId !== $this->getVisitor()->getVisitorId()) {
            throw new Exception(sprintf(
                self::VISITOR_ID_MISMATCH_ERROR,
                $visitorId,
                $this->getVisitor()->getVisitorId()
            ));
        }

        if (!isset($data[self::CAMPAIGNS])) {
            return true;
        }

        $campaigns = $data[self::CAMPAIGNS];
        if (!is_array($campaigns)) {
             return false;
        }

        foreach ($campaigns as $item) {
            if (
                !isset(
                    $item[self::CAMPAIGN_ID],
                    $item[self::CAMPAIGN_TYPE],
                    $item[self::VARIATION_GROUP_ID],
                    $item[self::VARIATION_ID]
                )
            ) {
                return false;
            }
        }

        return true;
    }


    /**
     * @param  array $item
     * @return boolean
     * @throws Exception
     */
    private function checkLookupVisitorData(array $item)
    {
        if (isset($item[self::VERSION]) && $item[self::VERSION] == 1) {
            return $this->checkLookupVisitorDataV1($item);
        }

        return false;
    }


    /**
     * @return void
     */
    public function lookupVisitor()
    {
        try {
            $visitorCacheInstance = $this->getConfig()->getVisitorCacheImplementation();
            if (!$visitorCacheInstance) {
                return;
            }

            $visitorCache = $visitorCacheInstance->lookupVisitor($this->visitor->getVisitorId());

            if (!is_array($visitorCache)) {
                return;
            }

            if (!$this->checkLookupVisitorData($visitorCache)) {
                throw new Exception(self::LOOKUP_VISITOR_JSON_OBJECT_ERROR);
            }

            $this->getVisitor()->visitorCache = $visitorCache;
        } catch (Exception $exception) {
            $this->logError($this->getConfig(), $exception->getMessage(), [FlagshipConstant::TAG => __FUNCTION__]);
        }
    }


    /**
     * @return void
     */
    public function cacheVisitor()
    {
        try {
            $visitorCacheInstance = $this->getConfig()->getVisitorCacheImplementation();
            if (!$visitorCacheInstance) {
                return;
            }

            $visitor            = $this->getVisitor();
            $assignmentsHistory = [];
            $campaigns          = [];

            foreach ($visitor->campaigns as $campaign) {
                $variation     = $campaign[FlagshipField::FIELD_VARIATION];
                $modifications = $variation[FlagshipField::FIELD_MODIFICATIONS];
                $assignmentsHistory[$campaign[FlagshipField::FIELD_VARIATION_GROUP_ID]] =
                    $variation[FlagshipField::FIELD_ID];

                $campaigns[] = [
                    FlagshipField::FIELD_CAMPAIGN_ID        => $campaign[FlagshipField::FIELD_ID],
                    FlagshipField::FIELD_SLUG               => isset($campaign[FlagshipField::FIELD_SLUG]) ?
                        $campaign[FlagshipField::FIELD_SLUG] : null,
                    FlagshipField::FIELD_VARIATION_GROUP_ID => $campaign[FlagshipField::FIELD_VARIATION_GROUP_ID],
                    FlagshipField::FIELD_VARIATION_ID       => $variation[FlagshipField::FIELD_ID],
                    FlagshipField::FIELD_IS_REFERENCE       => $variation[FlagshipField::FIELD_REFERENCE],
                    FlagshipField::FIELD_CAMPAIGN_TYPE      => $modifications[FlagshipField::FIELD_CAMPAIGN_TYPE],
                    self::ACTIVATED                         => false,
                    self::FLAGS                             => $modifications[FlagshipField::FIELD_VALUE],
                ];
            }

            if (
                isset(
                    $visitor->visitorCache,
                    $visitor->visitorCache[self::DATA],
                    $visitor->visitorCache[self::DATA][self::ASSIGNMENTS_HISTORY]
                )
            ) {
                $assignmentsHistory = array_merge(
                    $visitor->visitorCache[self::DATA][self::ASSIGNMENTS_HISTORY],
                    $assignmentsHistory
                );
            }

            $data = [
                self::VERSION => self::CURRENT_VERSION,
                self::DATA    => [
                    self::VISITOR_ID          => $visitor->getVisitorId(),
                    self::ANONYMOUS_ID        => $visitor->getAnonymousId(),
                    self::CONSENT             => $visitor->hasConsented(),
                    self::CONTEXT             => $visitor->getContext(),
                    self::CAMPAIGNS           => $campaigns,
                    self::ASSIGNMENTS_HISTORY => $assignmentsHistory,
                ],
            ];

            $visitorCacheInstance->cacheVisitor($visitor->getVisitorId(), $data);

            $visitor->visitorCache = $data;
        } catch (Exception $exception) {
            $this->logError($this->getConfig(), $exception->getMessage(), [FlagshipConstant::TAG => __FUNCTION__]);
        }//end try
    }


    /**
     * @return void
     */
    public function flushVisitor()
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

    public function sendTroubleshootingHit(Troubleshooting $hit)
    {
        $this->getTrackingManager()->addTroubleshootingHit($hit);
    }

    /**
     * @return DateTime
     */
    public function getCurrentDateTime()
    {
        return new DateTime();
    }

    public function sendSdkConfigAnalyticHit()
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
            ->setSdkConfigLogLeve($config->getLogLevel())
            ->setSdkConfigTimeout($config->getTimeout())
            ->setSdkConfigTrackingManagerConfigStrategy($config->getCacheStrategy())
            ->setSdkConfigBucketingUrl($bucketingUrl)
            ->setSdkConfigFetchThirdPartyData($fetchThirdPartyData)
            ->setSdkConfigUsingOnVisitorExposed(!!$config->getOnVisitorExposed())
            ->setSdkConfigUsingCustomHitCache(!!$config->getHitCacheImplementation())
            ->setSdkConfigUsingCustomVisitorCache(!!$config->getVisitorCacheImplementation())
            ->setSdkStatus($visitor->getSdkStatus())
            ->setFlagshipInstanceId($this->getFlagshipInstanceId())
            ->setVisitorId($this->getFlagshipInstanceId())
            ->setConfig($config);
        $this->getTrackingManager()->addUsageHit($analytic);
    }

    /**
     * /**
     * @param TroubleshootingData $troubleshootingData
     * @param FlagDTO[] $flagsDTO
     * @param array $campaigns
     * @param numeric $now
     * @return void
     * /
     * @return void
     */
    public function sendFetchFlagsTroubleshooting($troubleshootingData, $flagsDTO, $campaigns, $now)
    {
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
            ->setSdkConfigLogLeve($config->getLogLevel())
            ->setSdkConfigTimeout($config->getTimeout())
            ->setSdkConfigBucketingUrl($bucketingUrl)
            ->setSdkConfigFetchThirdPartyData($fetchThirdPartyData)
            ->setSdkConfigUsingOnVisitorExposed(!!$config->getOnVisitorExposed())
            ->setSdkConfigUsingCustomHitCache(!!$config->getHitCacheImplementation())
            ->setSdkConfigUsingCustomVisitorCache(!!$config->getVisitorCacheImplementation())
            ->setSdkConfigTrackingManagerConfigStrategy($config->getCacheStrategy())
            ->setVisitorId($visitor->getVisitorId())
            ->setAnonymousId($visitor->getAnonymousId())
            ->setConfig($config);

        $this->sendTroubleshootingHit($troubleshootingHit);
    }

    public function sendConsentHitTroubleshooting()
    {
        $consentHitTroubleshooting = $this->getVisitor()->getConsentHitTroubleshooting();
        if (!$consentHitTroubleshooting) {
            return;
        }
        $consentHitTroubleshooting->setTraffic($this->getVisitor()->getTraffic());
        $this->sendTroubleshootingHit($consentHitTroubleshooting);
        $this->getVisitor()->setConsentHitTroubleshooting(null);
    }
}
