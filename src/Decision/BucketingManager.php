<?php

namespace Flagship\Decision;

use DateTime;
use Exception;
use Flagship\Config\BucketingConfig;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\LogLevel;
use Flagship\Enum\TroubleshootingLabel;
use Flagship\Hit\Segment;
use Flagship\Hit\Troubleshooting;
use Flagship\Model\TroubleshootingData;
use Flagship\Utils\HttpClientInterface;
use Flagship\Utils\MurmurHash;
use Flagship\Visitor\VisitorAbstract;
use Flagship\Visitor\StrategyAbstract;

class BucketingManager extends DecisionManagerAbstract
{
    public const NB_MIN_CONTEXT_KEYS = 4;
    public const INVALID_BUCKETING_FILE_URL = "Invalid bucketing file url";
    public const GET_THIRD_PARTY_SEGMENT = 'GET_THIRD_PARTY_SEGMENT';
    public const THIRD_PARTY_SEGMENT = 'THIRD_PARTY_SEGMENT';
    public const PARTNER = "partner";
    public const SEGMENT = "segment";
    public const VALUE = "value";
    /**
     * @var MurmurHash
     */
    private MurmurHash $murmurHash;

    /**
     * @var BucketingConfig
     */
    protected FlagshipConfig $config;

    /**
     * @var Troubleshooting
     */
    protected Troubleshooting $troubleshootingHit;

    /**
     * @return BucketingConfig
     */
    public function getConfig(): FlagshipConfig
    {
        return $this->config;
    }

    /**
     * @param FlagshipConfig $config
     * @return BucketingManager
     */
    public function setConfig(FlagshipConfig $config): static
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param HttpClientInterface $httpClient
     * @param BucketingConfig $config
     * @param MurmurHash $murmurHash
     */
    public function __construct(HttpClientInterface $httpClient, BucketingConfig $config, MurmurHash $murmurHash)
    {
        parent::__construct($httpClient, $config);
        $this->murmurHash = $murmurHash;
    }

    /**
     * @param VisitorAbstract $visitor
     * @return void
     */
    protected function sendContext(VisitorAbstract $visitor): void
    {
        if (count($visitor->getContext()) <= self::NB_MIN_CONTEXT_KEYS || !$visitor->hasConsented()|| !$visitor->getHasContextBeenUpdated()) {
            return;
        }

        $visitor->setHasContextBeenUpdated(false);

        $segmentHit = new Segment($visitor->getContext());
        $visitor->sendHit($segmentHit);
    }

    /**
     * @param string $visitorId
     * @return array
     */
    protected function getThirdPartySegment(string $visitorId): array
    {
        $url = sprintf(FlagshipConstant::THIRD_PARTY_SEGMENT_URL, $this->getConfig()->getEnvId(), $visitorId);
        $now = $this->getNow();
        $context = [];
        try {
            $response = $this->httpClient->get($url);
            $content = $response->getBody();
            foreach ($content as $item) {
                $key = $item[self::PARTNER] . "::" . $item[self::SEGMENT];
                $context[$key] = $item[self::VALUE];
            }
            $this->logDebugSprintf(
                $this->config,
                self::GET_THIRD_PARTY_SEGMENT,
                FlagshipConstant::FETCH_THIRD_PARTY_SUCCESS,
                [
                    self::THIRD_PARTY_SEGMENT,
                    $this->getLogFormat(
                        null,
                        $url,
                        [],
                        [],
                        $this->getNow() - $now,
                        $response->getHeaders(),
                        $response->getBody(),
                        $response->getStatusCode()
                    )
                ]
            );
        } catch (Exception $exception) {
            $this->logErrorSprintf(
                $this->getConfig(),
                self::GET_THIRD_PARTY_SEGMENT,
                FlagshipConstant::UNEXPECTED_ERROR_OCCURRED,
                [
                 self::THIRD_PARTY_SEGMENT,
                 $this->getLogFormat(
                     $exception->getMessage(),
                     $url,
                     [],
                     [],
                     $this->getNow() - $now
                 ),
                ]
            );
        }
        return $context;
    }

    /**
     * @return mixed|null
     */
    protected function getBucketingFile(): mixed
    {
        $now = $this->getNow();
        $url = $this->getConfig()->getSyncAgentUrl();
        try {
            $this->httpClient->setTimeout($this->getConfig()->getTimeout() / 1000);
            if (!$url) {
                throw  new Exception(self::INVALID_BUCKETING_FILE_URL);
            }
            $response = $this->httpClient->get($url);

            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::SDK_BUCKETING_FILE)
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setTraffic(0)
                ->setLogLevel(LogLevel::INFO)
                ->setHttpRequestMethod("GET")
                ->setHttpRequestUrl($url)
                ->setHttpResponseBody($response->getBody())
                ->setHttpResponseHeaders($response->getHeaders())
                ->setHttpResponseCode($response->getStatusCode())
                ->setHttpResponseTime($this->getNow() - $now)
                ->setVisitorId($this->getFlagshipInstanceId())
                ->setConfig($this->getConfig());
            $this->troubleshootingHit = $troubleshooting;
            return $response->getBody();
        } catch (Exception $exception) {
            $this->logError($this->getConfig(), $exception->getMessage(), [FlagshipConstant::TAG => __FUNCTION__]);
            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::SDK_BUCKETING_FILE_ERROR)
                ->setFlagshipInstanceId($this->getFlagshipInstanceId())
                ->setHttpRequestMethod("GET")
                ->setHttpRequestUrl($url)
                ->setHttpResponseBody($exception->getMessage())
                ->setHttpResponseTime($this->getNow() - $now)
                ->setTraffic(0)
                ->setLogLevel(LogLevel::ERROR)
                ->setConfig($this->getConfig())
                ->setVisitorId($this->getFlagshipInstanceId());
            $this->troubleshootingHit = $troubleshooting;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getCampaigns(VisitorAbstract $visitor): array|null
    {
        $bucketingCampaigns = $this->getBucketingFile();

        if (!$bucketingCampaigns) {
            return [];
        }
        $this->troubleshootingData = null;
        if (isset($bucketingCampaigns[FlagshipField::ACCOUNT_SETTINGS][FlagshipField::TROUBLESHOOTING])) {
            $troubleshooting = $bucketingCampaigns[FlagshipField::ACCOUNT_SETTINGS][FlagshipField::TROUBLESHOOTING];

            $troubleshootingData = new TroubleshootingData();

            if (isset($troubleshooting[FlagshipField::START_DATE])) {
                $startDate = new DateTime($troubleshooting[FlagshipField::START_DATE]);
                $troubleshootingData->setStartDate($startDate);
            }
            if (isset($troubleshooting[FlagshipField::END_DATE])) {
                $endDate = new DateTime($troubleshooting[FlagshipField::END_DATE]);
                $troubleshootingData->setEndDate($endDate);
            }
            if (isset($troubleshooting[FlagshipField::TRAFFIC])) {
                $troubleshootingData->setTraffic($troubleshooting[FlagshipField::TRAFFIC]);
            }
            if (isset($troubleshooting[FlagshipField::TIMEZONE])) {
                $troubleshootingData->setTimezone($troubleshooting[FlagshipField::TIMEZONE]);
            }
            $this->troubleshootingData = $troubleshootingData;
            $this->getTrackingManager()->setTroubleshootingData($troubleshootingData);
            $this->getTrackingManager()->addTroubleshootingHit($this->troubleshootingHit);
        }

        if (isset($bucketingCampaigns[FlagshipField::FIELD_PANIC])) {
            $hasPanicMode = !empty($bucketingCampaigns[FlagshipField::FIELD_PANIC]);
            $this->setIsPanicMode($hasPanicMode);
            return [];
        }

        $this->setIsPanicMode(false);

        if (!isset($bucketingCampaigns[FlagshipField::FIELD_CAMPAIGNS])) {
            return [];
        }

        $campaigns = $bucketingCampaigns[FlagshipField::FIELD_CAMPAIGNS];

        $visitorCampaigns = [];

        if ($this->getConfig()->getFetchThirdPartyData()) {
            $thirdPartySegments = $this->getThirdPartySegment($visitor->getVisitorId());
            $visitor->updateContextCollection($thirdPartySegments);
        }

        $this->sendContext($visitor);

        foreach ($campaigns as $campaign) {
            if (!isset($campaign[FlagshipField::FIELD_VARIATION_GROUPS])) {
                continue;
            }
            $variationGroups = $campaign[FlagshipField::FIELD_VARIATION_GROUPS];
            $currentCampaigns = $this->getVisitorCampaigns(
                $variationGroups,
                $campaign[FlagshipField::FIELD_ID],
                $visitor,
                $campaign[FlagshipField::FIELD_CAMPAIGN_TYPE],
                $campaign[FlagshipField::FIELD_SLUG] ?? null,
                $campaign[FlagshipField::FIELD_NANE] ?? null
            );
            $visitorCampaigns = array_merge($visitorCampaigns, $currentCampaigns);
        }
        return $visitorCampaigns;
    }

    /**
     * @param array $variationGroups
     * @param string $campaignId
     * @param VisitorAbstract $visitor
     * @param string $campaignType
     * @param ?string $slug
     * @param ?string $campaignName
     * @return array
     */
    private function getVisitorCampaigns(
        array $variationGroups,
        string $campaignId,
        VisitorAbstract $visitor,
        string $campaignType,
        ?string $slug,
        ?string $campaignName
    ): array {
        $visitorCampaigns = [];
        foreach ($variationGroups as $variationGroup) {
            if ($this->isMatchTargeting($variationGroup, $visitor)) {
                $variations = $this->getVariation(
                    $variationGroup,
                    $visitor
                );
                $visitorCampaigns[] = [
                                       FlagshipField::FIELD_ID                   => $campaignId,
                                       FlagshipField::FIELD_NANE                 => $campaignName,
                                       FlagshipField::FIELD_SLUG                 => $slug,
                                       FlagshipField::FIELD_VARIATION_GROUP_ID   => $variationGroup[FlagshipField::FIELD_ID],
                                       FlagshipField::FIELD_VARIATION_GROUP_NAME => $variationGroup[FlagshipField::FIELD_NANE] ?? null,
                                       FlagshipField::FIELD_VARIATION            => $variations,
                                       FlagshipField::FIELD_CAMPAIGN_TYPE        => $campaignType,
                                      ];
                break;
            }
        }
        return $visitorCampaigns;
    }

    /**
     * @param string $variationGroupId
     * @param VisitorAbstract $visitor
     * @return mixed|null
     */
    private function getVisitorAssignmentsHistory(string $variationGroupId, VisitorAbstract $visitor): mixed
    {
        return $visitor->visitorCache[StrategyAbstract::DATA][StrategyAbstract::ASSIGNMENTS_HISTORY][$variationGroupId] ?? null;
    }

    private function findVariationById(array $variations, $key)
    {
        foreach ($variations as $item) {
            if ($item[FlagshipField::FIELD_ID] === $key) {
                return $item;
            }
        }
        return null;
    }

    /**
     *
     * @param array $variationGroup
     * @param VisitorAbstract $visitor
     * @return array
     */
    private function getVariation(array $variationGroup, VisitorAbstract $visitor): array
    {
        $visitorVariation = [];
        if (!isset($variationGroup[FlagshipField::FIELD_ID])) {
            return $visitorVariation;
        }
        $groupVariationId = $variationGroup[FlagshipField::FIELD_ID];
        $hash = $this->murmurHash->murmurHash3Int32($groupVariationId . $visitor->getVisitorId());
        $hashAllocation = $hash % 100;
        $variations = $variationGroup[FlagshipField::FIELD_VARIATIONS];
        $totalAllocation = 0;

        foreach ($variations as $variation) {
            if (!isset($variation[FlagshipField::FIELD_ALLOCATION])) {
                continue;
            }
            $assignmentsVariationId = $this->getVisitorAssignmentsHistory($groupVariationId, $visitor);
            if ($assignmentsVariationId) {
                $newVariation = $this->findVariationById($variations, $assignmentsVariationId);
                if (!$newVariation) {
                    continue;
                }
                $visitorVariation = [
                                     FlagshipField::FIELD_ID            => $newVariation[FlagshipField::FIELD_ID],
                                     FlagshipField::FIELD_MODIFICATIONS => $newVariation[FlagshipField::FIELD_MODIFICATIONS],
                                     FlagshipField::FIELD_REFERENCE     => !empty($newVariation[FlagshipField::FIELD_REFERENCE]),
                                     FlagshipField::FIELD_NANE          => $newVariation[FlagshipField::FIELD_NANE] ?? null,
                                    ];
                break;
            }

            if (!isset($variation[FlagshipField::FIELD_ALLOCATION]) || $variation[FlagshipField::FIELD_ALLOCATION] <= 0) {
                continue;
            }

            $totalAllocation += $variation[FlagshipField::FIELD_ALLOCATION];
            if ($hashAllocation < $totalAllocation) {
                $visitorVariation = [
                                     FlagshipField::FIELD_ID            => $variation[FlagshipField::FIELD_ID],
                                     FlagshipField::FIELD_MODIFICATIONS => $variation[FlagshipField::FIELD_MODIFICATIONS],
                                     FlagshipField::FIELD_REFERENCE     => !empty($variation[FlagshipField::FIELD_REFERENCE]),
                                     FlagshipField::FIELD_NANE          => $variation[FlagshipField::FIELD_NANE] ?? null,
                                    ];
                break;
            }
        }

        return $visitorVariation;
    }

    /**
     * @param array $variationGroup
     * @param VisitorAbstract $visitor
     * @return bool
     */
    private function isMatchTargeting(array $variationGroup, VisitorAbstract $visitor): bool
    {
        if (!isset($variationGroup[FlagshipField::FIELD_TARGETING])) {
            return false;
        }

        $targeting = $variationGroup[FlagshipField::FIELD_TARGETING];

        if (!isset($targeting[FlagshipField::FIELD_TARGETING_GROUPS])) {
            return false;
        }

        $targetingGroups = $targeting[FlagshipField::FIELD_TARGETING_GROUPS];

        foreach ($targetingGroups as $targetingGroup) {
            if (!isset($targetingGroup[FlagshipField::FIELD_TARGETINGS])) {
                continue;
            }

            $innerTargetings = $targetingGroup[FlagshipField::FIELD_TARGETINGS];

            if ($this->checkAndTargeting($innerTargetings, $visitor)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $innerTargetings
     * @param VisitorAbstract $visitor
     * @return bool
     */
    private function checkAndTargeting(array $innerTargetings, VisitorAbstract $visitor): bool
    {
        $isMatching = false;
        foreach ($innerTargetings as $innerTargeting) {
            $key = $innerTargeting['key'];
            $operator = $innerTargeting["operator"];
            $targetingValue = $innerTargeting["value"];
            $visitorContext = $visitor->getContext();

            if ($operator === "EXISTS") {
                if (array_key_exists($key, $visitorContext)) {
                    $isMatching = true;
                    continue;
                }
                $isMatching = false;
                break;
            }
            if ($operator === "NOT_EXISTS") {
                if (array_key_exists($key, $visitorContext)) {
                    $isMatching = false;
                    break;
                }
                $isMatching = true;
                continue;
            }

            switch ($key) {
                case "fs_all_users":
                    $isMatching = true;
                    continue 2;
                case "fs_users":
                    $contextValue = $visitor->getVisitorId();
                    break;
                default:
                    if (!isset($visitorContext[$key])) {
                        $isMatching = false;
                        break 2;
                    }
                    $contextValue = $visitorContext[$key];
                    break;
            }

            $isMatching = $this->testOperator($operator, $contextValue, $targetingValue);
            if (!$isMatching) {
                break;
            }
        }

        return $isMatching;
    }

    /**
     * @param $operator
     * @return bool
     */
    private function isANDListOperator($operator): bool
    {
        return in_array($operator, ['NOT_EQUALS', 'NOT_CONTAINS']);
    }

    /**
     * @param string $operator
     * @param mixed $contextValue
     * @param array $targetingValue
     * @param bool $initialCheck
     * @return bool|mixed
     */
    private function testListOperatorLoop(
        string $operator,
        mixed $contextValue,
        array $targetingValue,
        bool $initialCheck
    ): mixed {
        $check = $initialCheck;
        foreach ($targetingValue as $value) {
            $check = $this->testOperator($operator, $contextValue, $value);
            if ($check !== $initialCheck) {
                break;
            }
        }
        return $check;
    }

    /**
     * @param string $operator
     * @param mixed $contextValue
     * @param array $targetingValue
     * @return bool
     */
    private function testListOperator(string $operator, mixed $contextValue, array $targetingValue): bool
    {
        $andOperator = $this->isANDListOperator($operator);
        if ($andOperator) {
            $check = $this->testListOperatorLoop($operator, $contextValue, $targetingValue, true);
        } else {
            $check = $this->testListOperatorLoop($operator, $contextValue, $targetingValue, false);
        }
        return $check;
    }

    /**
     * @param string $operator
     * @param mixed $contextValue
     * @param mixed $targetingValue
     * @return bool
     */
    private function testOperator(string $operator, mixed $contextValue, mixed $targetingValue): bool
    {

        if (is_array($targetingValue)) {
            return $this->testListOperator($operator, $contextValue, $targetingValue);
        }
        return match ($operator) {
            "EQUALS" => $contextValue === $targetingValue,
            "NOT_EQUALS" => $contextValue !== $targetingValue,
            "CONTAINS" => str_contains(strval($contextValue), strval($targetingValue)),
            "NOT_CONTAINS" => !str_contains(strval($contextValue), strval($targetingValue)),
            "GREATER_THAN" => $contextValue > $targetingValue,
            "LOWER_THAN" => $contextValue < $targetingValue,
            "GREATER_THAN_OR_EQUALS" => $contextValue >= $targetingValue,
            "LOWER_THAN_OR_EQUALS" => $contextValue <= $targetingValue,
            "STARTS_WITH" => (bool)preg_match("/^$targetingValue/i", $contextValue),
            "ENDS_WITH" => (bool)preg_match("/$targetingValue$/i", $contextValue),
            default => false,
        };
    }
}
