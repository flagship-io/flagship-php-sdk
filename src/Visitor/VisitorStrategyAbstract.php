<?php

namespace Flagship\Visitor;

use Exception;
use Flagship\Api\TrackingManagerAbstract;
use Flagship\Config\FlagshipConfig;
use Flagship\Decision\DecisionManagerAbstract;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Traits\HasSameTypeTrait;
use Flagship\Traits\ValidatorTrait;
use Flagship\Utils\ConfigManager;

abstract class VisitorStrategyAbstract implements VisitorCoreInterface, VisitorFlagInterface
{
    use ValidatorTrait;
    use HasSameTypeTrait;

    const DATA = "data";
    const CAMPAIGNS = 'campaigns';
    const VISITOR_ID = 'visitorId';
    const VISITOR_ID_MISMATCH_ERROR = "Visitor ID mismatch: '%s' vs '%s'";
    const CAMPAIGN_ID = "campaignId";
    const CAMPAIGN_TYPE = "type";
    const VARIATION_GROUP_ID = "variationGroupId";
    const VARIATION_ID = "variationId";
    const LOOKUP_VISITOR_JSON_OBJECT_ERROR = 'JSON DATA must fit the type VisitorCache';
    const VERSION = "version";
    const CURRENT_VERSION = 1;
    const ASSIGNMENTS_HISTORY = 'assignmentsHistory';
    const FLAGS = "flags";
    const ACTIVATED = "activated";
    const ANONYMOUS_ID = 'anonymousId';
    const CONSENT = 'consent';
    const CONTEXT = 'context';
    /**
     * @var VisitorAbstract
     */
    protected $visitor;

    public function __construct(VisitorAbstract $visitor)
    {
        $this->visitor = $visitor;
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
     * @param string $process
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
     * @param string $process
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
        $data = $item[self::DATA];
        $visitorId = $data[self::VISITOR_ID];

        if ($visitorId !== $this->getVisitor()->getVisitorId()) {
            throw new Exception(sprintf(self::VISITOR_ID_MISMATCH_ERROR, $visitorId, $this->getVisitor()->getVisitorId()));
        }

        if (!isset($data[self::CAMPAIGNS])) {
            return  true;
        }

        $campaigns = $data[self::CAMPAIGNS];
        if (!is_array($campaigns)) {
             return  false;
        }

        foreach ($campaigns as $item) {
            if (!isset($item[self::CAMPAIGN_ID], $item[self::CAMPAIGN_TYPE], $item[self::VARIATION_GROUP_ID], $item[self::VARIATION_ID])) {
                return  false;
            }
        }

        return true;
    }

    /**
     * @param array $item
     * @return bool
     * @throws Exception
     */
    private function checkLookupVisitorData(array $item)
    {
        if (isset($item[self::VERSION]) && $item[self::VERSION] == 1) {
            return  $this->checkLookupVisitorDataV1($item);
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
                throw  new Exception(self::LOOKUP_VISITOR_JSON_OBJECT_ERROR);
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

            $visitor = $this->getVisitor();
            $assignmentsHistory = [];
            $campaigns = [];

            foreach ($visitor->campaigns as $campaign) {
                $variation = $campaign[FlagshipField::FIELD_VARIATION];
                $modifications = $variation[FlagshipField::FIELD_MODIFICATIONS];
                $assignmentsHistory[$campaign[FlagshipField::FIELD_VARIATION_GROUP_ID]] = $variation[FlagshipField::FIELD_ID];

                $campaigns[] = [
                    FlagshipField::FIELD_CAMPAIGN_ID => $campaign[FlagshipField::FIELD_ID],
                    FlagshipField::FIELD_SLUG => isset($campaign[FlagshipField::FIELD_SLUG]) ? $campaign[FlagshipField::FIELD_SLUG] : null,
                    FlagshipField::FIELD_VARIATION_GROUP_ID => $campaign[FlagshipField::FIELD_VARIATION_GROUP_ID],
                    FlagshipField::FIELD_VARIATION_ID => $variation[FlagshipField::FIELD_ID],
                    FlagshipField::FIELD_IS_REFERENCE => $variation[FlagshipField::FIELD_REFERENCE],
                    FlagshipField::FIELD_CAMPAIGN_TYPE => $modifications[FlagshipField::FIELD_CAMPAIGN_TYPE],
                    self::ACTIVATED => false,
                    self::FLAGS => $modifications[FlagshipField::FIELD_VALUE]
                ];
            }

            if (isset($visitor->visitorCache, $visitor->visitorCache[self::DATA], $visitor->visitorCache[self::DATA][self::ASSIGNMENTS_HISTORY])) {
                $assignmentsHistory = array_merge($visitor->visitorCache[self::DATA][self::ASSIGNMENTS_HISTORY], $assignmentsHistory);
            }

            $data = [
                self::VERSION => self::CURRENT_VERSION,
                self::DATA => [
                    self::VISITOR_ID => $visitor->getVisitorId(),
                    self::ANONYMOUS_ID => $visitor->getAnonymousId(),
                    self::CONSENT => $visitor->hasConsented(),
                    self::CONTEXT => $visitor->getContext(),
                    self::CAMPAIGNS => $campaigns,
                    self::ASSIGNMENTS_HISTORY =>  $assignmentsHistory
                ]
            ];

            $visitorCacheInstance->cacheVisitor($visitor->getVisitorId(), $data);

            $visitor->visitorCache = $data;
        } catch (Exception $exception) {
            $this->logError($this->getConfig(), $exception->getMessage(), [FlagshipConstant::TAG => __FUNCTION__]);
        }
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
}
