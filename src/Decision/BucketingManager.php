<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Hit\Segment;
use Flagship\Enum\LogLevel;
use Flagship\Utils\MurmurHash;
use Flagship\Model\CampaignDTO;
use Flagship\Enum\FlagshipField;
use Flagship\Model\BucketingDTO;
use Flagship\Model\VariationDTO;
use Flagship\Hit\Troubleshooting;
use Flagship\Model\TargetingsDTO;
use Flagship\Enum\FlagshipConstant;
use Flagship\Config\BucketingConfig;
use Flagship\Enum\TargetingOperator;
use Flagship\Model\TargetingGroupDTO;
use Flagship\Model\VariationGroupDTO;
use Flagship\Visitor\VisitorAbstract;
use Flagship\Enum\TroubleshootingLabel;
use Flagship\Utils\HttpClientInterface;
use Flagship\Model\BucketingCampaignDTO;
use Flagship\Model\BucketingVariationDTO;

/**
 * @phpstan-import-type FlagValue from \Flagship\Model\Types
 */
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
     * @var Troubleshooting
     */
    protected Troubleshooting $troubleshootingHit;

    /**
     * @return BucketingConfig
     */
    public function getConfig(): BucketingConfig
    {
        /** @var BucketingConfig */
        return $this->config;
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
        if (count($visitor->getContext()) <= self::NB_MIN_CONTEXT_KEYS || !$visitor->hasConsented() || !$visitor->getHasContextBeenUpdated()) {
            return;
        }

        $visitor->setHasContextBeenUpdated(false);

        $segmentHit = new Segment($visitor->getContext(), $this->getConfig());
        $visitor->sendHit($segmentHit);
    }

    /**
     * @param string $visitorId
     * @return array<string, scalar>
     */
    protected function getThirdPartySegment(string $visitorId): array
    {
        $url = sprintf(FlagshipConstant::THIRD_PARTY_SEGMENT_URL, $this->getConfig()->getEnvId(), $visitorId);
        $now = $this->getNow();
        $context = [];
        try {
            $response = $this->httpClient->get($url);
            /**
             * @var array<array<string, scalar>>
             */
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
     * @return BucketingDTO|null
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
                ->setVisitorId($this->getFlagshipInstanceId() ?? '')
                ->setConfig($this->getConfig());
            $this->troubleshootingHit = $troubleshooting;
            /** @var array<mixed>|null */
            $body = $response->getBody();
            if (!is_array($body)) {
                return null;
            }
            return BucketingDTO::fromArray($body);
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
                ->setVisitorId($this->getFlagshipInstanceId() ?? "");
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

        $troubleshooting = $bucketingCampaigns->getAccountSettings()?->getTroubleshooting();

        if ($troubleshooting) {
            $troubleshootingData = $troubleshooting->toTroubleshootingData();
            $this->troubleshootingData = $troubleshootingData;
            if ($troubleshootingData) {
                $this->getTrackingManager()->setTroubleshootingData($troubleshootingData);
                $this->getTrackingManager()->addTroubleshootingHit($this->troubleshootingHit);
            }
        }

        if ($bucketingCampaigns->getPanic() !== null) {
            $this->setIsPanicMode($bucketingCampaigns->getPanic());
            return [];
        }

        $this->setIsPanicMode(false);

        $campaigns = $bucketingCampaigns->getCampaigns();

        if (empty($campaigns)) {
            return [];
        }

        $visitorCampaigns = [];

        if ($this->getConfig()->getFetchThirdPartyData()) {
            $thirdPartySegments = $this->getThirdPartySegment($visitor->getVisitorId());
            $visitor->updateContextCollection($thirdPartySegments);
        }

        $this->sendContext($visitor);

        foreach ($campaigns as $campaign) {
            $currentCampaigns = $this->getVisitorCampaigns(
                $visitor,
                $campaign
            );
            if ($currentCampaigns !== null) {
                $visitorCampaigns[] = $currentCampaigns;
            }
        }
        return $visitorCampaigns;
    }

    /**
     * @param VisitorAbstract $visitor
     * @param BucketingCampaignDTO $campaign
     * @return CampaignDTO|null
     */
    private function getVisitorCampaigns(
        VisitorAbstract $visitor,
        BucketingCampaignDTO $campaign
    ): CampaignDTO|null {
        $variationGroups = $campaign->getVariationGroups();
        $campaignId = $campaign->getId();
        $campaignType = $campaign->getType();
        foreach ($variationGroups as $variationGroup) {
            if ($this->checkVisitorMatchesTargeting($variationGroup, $visitor)) {
                $variation = $this->getVariation(
                    $variationGroup,
                    $visitor
                );
                if (empty($variation)) {
                    return null;
                }
                $newCampaign = new CampaignDTO(
                    $campaignId,
                    $variationGroup->getId(),
                    $variation
                );
                $newCampaign->setName($campaign->getName());
                $newCampaign->setSlug($campaign->getSlug());
                $newCampaign->setType($campaignType);
                $newCampaign->setVariationGroupName(
                    $variationGroup->getName()
                );
                return $newCampaign;
            }
        }
        return  null;
    }

    /**
     * @param string $variationGroupId
     * @param VisitorAbstract $visitor
     * @return string|null
     */
    private function getVisitorAssignmentsHistory(string $variationGroupId, VisitorAbstract $visitor): string|null
    {
        $assignmentsHistory = $visitor->visitorCache?->getData()->getAssignmentsHistory();
        return $assignmentsHistory[$variationGroupId] ?? null;
    }

    /**
     * 
     * @param BucketingVariationDTO[] $variations
     * @param string $assignmentsVariationId
     */
    private function findVariationById(array $variations, string $assignmentsVariationId): BucketingVariationDTO|null
    {
        return $this->array_find(
            $variations,
            fn(BucketingVariationDTO $variation) => $variation->getId() === $assignmentsVariationId
        );
    }

    /**
     *
     * @param VariationGroupDTO $variationGroup
     * @param VisitorAbstract $visitor
     * @return VariationDTO|null
     */
    private function getVariation(VariationGroupDTO $variationGroup, VisitorAbstract $visitor): VariationDTO|null
    {
        $visitorVariation = [];
        if (empty($variationGroup->getId())) {
            return null;
        }

        $groupVariationId = $variationGroup->getId();
        $hash = $this->murmurHash->murmurHash3Int32($groupVariationId . $visitor->getVisitorId());
        $hashAllocation = $hash % 100;
        $variations = $variationGroup->getVariations();
        $totalAllocation = 0;

        foreach ($variations as $variation) {
            if ($variation->getAllocation() === null) {
                continue;
            }
            $assignmentsVariationId = $this->getVisitorAssignmentsHistory($groupVariationId, $visitor);
            if ($assignmentsVariationId) {
                $assignedVariation = $this->findVariationById($variations, $assignmentsVariationId);
                if (!$assignedVariation) {
                    continue;
                }
                $visitorVariation = new VariationDTO(
                    $assignedVariation->getId(),
                    $variation->getModifications()
                );
                $visitorVariation->setReference($assignedVariation->getReference());
                $visitorVariation->setName($assignedVariation->getName());
                return $visitorVariation;
            }

            if ($variation->getAllocation() <= 0) {
                continue;
            }

            $totalAllocation += $variation->getAllocation();
            if ($hashAllocation < $totalAllocation) {
                $visitorVariation = new VariationDTO(
                    $variation->getId(),
                    $variation->getModifications()
                );
                $visitorVariation->setReference($variation->getReference());
                $visitorVariation->setName($variation->getName());

                return $visitorVariation;
            }
        }

        return null;
    }

    /**
     * @param VariationGroupDTO $variationGroup
     * @param VisitorAbstract $visitor
     * @return bool
     */
    private function checkVisitorMatchesTargeting(VariationGroupDTO $variationGroup, VisitorAbstract $visitor): bool
    {
        $targetingGroups = $variationGroup->getTargeting()->getTargetingGroups();
        if (empty($targetingGroups)) {
            return false;
        }

        // OR logic: at least one targeting group must match
        return $this->array_any(
            $targetingGroups,
            fn(TargetingGroupDTO $targetingGroup)
            => $this->checkAllTargetingRulesMatch(
                $targetingGroup->getTargetings(),
                $visitor
            )
        );
    }

    /**
     * 
     * @param TargetingsDTO[] $targetings
     * @return bool
     */
    private function checkAllTargetingRulesMatch(array $targetings, VisitorAbstract $visitor): bool
    {
        if (empty($targetings)) {
            return false;
        }

        // AND logic: ALL targeting rules must match
        return $this->array_all(
            $targetings,
            fn(TargetingsDTO $targeting)
            => $this->matchesTargetingCriteria(
                $targeting,
                $visitor
            )
        );
    }


    private function matchesArrayTargeting(TargetingsDTO $targeting, VisitorAbstract $visitor): bool
    {
        /**
         * @var array<mixed>
         */
        $targetingValue = $targeting->getValue();

        $notOperator = in_array(
            $targeting->getOperator()->value,
            [
                TargetingOperator::NOT_CONTAINS->value,
                TargetingOperator::NOT_EQUALS->value,
            ]
        );

        $cloneTargeting = clone $targeting;

        /**
         * 
         * @param FlagValue $value
         * @return void
         */
        $checkOperator = function (mixed $value) use ($cloneTargeting, $visitor): bool {
            $cloneTargeting->setValue($value);
            return $this->matchesTargetingCriteria($cloneTargeting, $visitor);
        };


        if ($notOperator) {
            return $this->array_all(
                $targetingValue,
                $checkOperator(...)
            );
        }

        return $this->array_any(
            $targetingValue,
            $checkOperator(...)
        );
    }

    private function matchesTargetingCriteria(TargetingsDTO $targeting, VisitorAbstract $visitor): bool
    {
        if ($targeting->getKey() === FlagshipField::FS_ALL_USERS) {
            return true;
        }

        if (is_array($targeting->getValue())) {
            return $this->matchesArrayTargeting($targeting, $visitor);
        }

        $visitorValue = $targeting->getKey() === FlagshipField::FS_USERS
            ? $visitor->getVisitorId()
            : $visitor->getContext()[$targeting->getKey()] ?? null;

        if ($visitorValue === null) {
            return $targeting->getOperator() === TargetingOperator::NOT_EXISTS;
        }

        return $this->evaluateOperator(
            $targeting->getOperator(),
            $visitorValue,
            $targeting->getValue()
        );
    }

    /**
     * 
     * @param TargetingOperator $operator
     * @param scalar $visitorValue
     * @param mixed $targetingValue
     * @return bool
     */
    private function evaluateOperator(
        TargetingOperator $operator,
        float|int|string|bool|null $visitorValue,
        mixed $targetingValue
    ): bool {

        $targetingValueStr = is_scalar($targetingValue) ? strval($targetingValue) : '';

        return match ($operator) {
            TargetingOperator::EQUALS => $visitorValue === $targetingValue,
            TargetingOperator::NOT_EQUALS => $visitorValue !== $targetingValue,
            TargetingOperator::EXISTS => $visitorValue !== null,
            TargetingOperator::CONTAINS => str_contains(strval($visitorValue), $targetingValueStr),
            TargetingOperator::NOT_CONTAINS => !str_contains(strval($visitorValue), $targetingValueStr),
            TargetingOperator::GREATER_THAN => $visitorValue > $targetingValue,
            TargetingOperator::LOWER_THAN => $visitorValue < $targetingValue,
            TargetingOperator::GREATER_THAN_OR_EQUALS => $visitorValue >= $targetingValue,
            TargetingOperator::LOWER_THAN_OR_EQUALS => $visitorValue <= $targetingValue,
            TargetingOperator::STARTS_WITH => (bool)preg_match("/^" . preg_quote($targetingValueStr, '/') . "/i", strval($visitorValue)),
            TargetingOperator::ENDS_WITH => (bool)preg_match("/" . preg_quote($targetingValueStr, '/') . "$/i", strval($visitorValue)),
            default => false,
        };
    }
}
