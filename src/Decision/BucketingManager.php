<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Config\BucketingConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Utils\HttpClientInterface;
use Flagship\Utils\MurmurHash;
use Flagship\Visitor\VisitorAbstract;

class BucketingManager extends DecisionManagerAbstract
{
    /**
     * @var MurmurHash
     */
    private $murmurHash;

    public function __construct(HttpClientInterface $httpClient, BucketingConfig $config, MurmurHash $murmurHash)
    {
        parent::__construct($httpClient, $config);
        $this->murmurHash = $murmurHash;
    }

    protected function sendContext(VisitorAbstract $visitor)
    {
        $envId = $this->getConfig()->getEnvId();
        $url = sprintf(FlagshipConstant::BUCKETING_API_CONTEXT_URL, $envId);
        $headers = $this->buildHeader($this->getConfig()->getApiKey());
        $this->httpClient->setHeaders($headers);
        $this->httpClient->setTimeout($this->getConfig()->getTimeout() / 1000);
        $postBody = [
            "visitorId" => $visitor->getVisitorId(),
            "type" => "CONTEXT",
            "data" => $visitor->getContext()
        ];
        try {
            $response =  $this->httpClient->post($url, $postBody);
            if ($response->getStatusCode() >= 400) {
                $this->logError($this->getConfig(), $response->getBody(), [
                    FlagshipConstant::TAG => __FUNCTION__
                ]);
            }
        } catch (Exception $exception) {
            $this->logError($this->getConfig(), $exception->getMessage(), [
                FlagshipConstant::TAG => __FUNCTION__
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function getCampaigns(VisitorAbstract $visitor)
    {
        $this->sendContext($visitor);
        $bucketingFile = $this->getConfig()->getBucketingDirectory() . "/bucketing.json";
        if (!file_exists($bucketingFile)) {
            return [];
        }
        $bucketingCampaigns = file_get_contents($bucketingFile);

        if (!$bucketingCampaigns) {
            return [];
        }

        $bucketingCampaigns = json_decode($bucketingCampaigns, true);

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

        foreach ($campaigns as $campaign) {
            if (!isset($campaign[FlagshipField::FIELD_VARIATION_GROUPS])) {
                continue;
            }
            $variationGroups = $campaign[FlagshipField::FIELD_VARIATION_GROUPS];
            $currentCampaigns = $this->getVisitorCampaigns(
                $variationGroups,
                $campaign[FlagshipField::FIELD_ID],
                $visitor
            );
            $visitorCampaigns = array_merge($visitorCampaigns, $currentCampaigns);
        }
        return $visitorCampaigns;
    }

    /**
     * @param $variationGroups
     * @param $campaignId
     * @param VisitorAbstract $visitor
     * @return array
     */
    private function getVisitorCampaigns($variationGroups, $campaignId, VisitorAbstract $visitor)
    {
        $visitorCampaigns = [];
        foreach ($variationGroups as $variationGroup) {
            $check = $this->isMatchTargeting($variationGroup, $visitor);
            if ($check) {
                $variations = $this->getVariation(
                    $variationGroup,
                    $visitor->getVisitorId()
                );
                $visitorCampaigns[] = [
                    FlagshipField::FIELD_ID => $campaignId,
                    FlagshipField::FIELD_VARIATION_GROUP_ID => $variationGroup[FlagshipField::FIELD_ID],
                    FlagshipField::FIELD_VARIATION => $variations
                ];
                break;
            }
        }
        return $visitorCampaigns;
    }

    /**
     *
     * @param array $variationGroup
     * @param string $visitorId
     * @return array
     */
    private function getVariation($variationGroup, $visitorId)
    {
        $visitorVariation = [];
        if (!isset($variationGroup[FlagshipField::FIELD_ID])) {
            return $visitorVariation;
        }
        $groupVariationId = $variationGroup[FlagshipField::FIELD_ID];
        $hash = $this->murmurHash->murmurHash3Int32($groupVariationId . $visitorId);
        $hashAllocation = $hash % 100;
        $variations = $variationGroup[FlagshipField::FIELD_VARIATIONS];
        $totalAllocation = 0;

        foreach ($variations as $variation) {
            if (!isset($variation[FlagshipField::FIELD_ALLOCATION])) {
                continue;
            }
            $totalAllocation += $variation[FlagshipField::FIELD_ALLOCATION];
            if ($hashAllocation < $totalAllocation) {
                $visitorVariation = [
                    FlagshipField::FIELD_ID => $variation[FlagshipField::FIELD_ID],
                    FlagshipField::FIELD_MODIFICATIONS => $variation[FlagshipField::FIELD_MODIFICATIONS],
                    FlagshipField::FIELD_REFERENCE => !empty($variation[FlagshipField::FIELD_REFERENCE])
                ];
                break;
            }
        }

        return $visitorVariation;
    }

    /**
     * @param mixed $variationGroup
     */
    private function isMatchTargeting($variationGroup, VisitorAbstract $visitor)
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

            $check = $this->checkAndTargeting($innerTargetings, $visitor);
            if ($check) {
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
    private function checkAndTargeting($innerTargetings, VisitorAbstract $visitor)
    {
        $isMatching = true;
        foreach ($innerTargetings as $innerTargeting) {
            $key = $innerTargeting['key'];
            $operator = $innerTargeting["operator"];
            $targetingValue = $innerTargeting["value"];
            $visitorContext = $visitor->getContext();

            switch ($key) {
                case "fs_all_users":
                    continue 2;
                case "fs_users":
                    $contextValue = $visitor->getVisitorId();
                    break;
                default:
                    if (!isset($visitorContext[$key])) {
                        return false;
                    }
                    $contextValue = $visitorContext[$key];
                    break;
            }

            $checkOperator = $this->testOperator($operator, $contextValue, $targetingValue);
            if (!$checkOperator) {
                return false;
            }
        }

        return $isMatching;
    }

    /**
     * @param string $operator
     * @param mixed $contextValue
     * @param mixed $targetingValue
     * @return bool
     */
    private function testOperator($operator, $contextValue, $targetingValue)
    {

        switch ($operator) {
            case "EQUALS":
                $check = $contextValue === $targetingValue;
                break;
            case "NOT_EQUALS":
                $check = $contextValue !== $targetingValue;
                break;
            case "CONTAINS":
                $targetingValueSting = join("|", $targetingValue);
                $check = (bool)preg_match("/{$targetingValueSting}/i", $contextValue);
                break;
            case "NOT_CONTAINS":
                $targetingValueSting = join("|", $targetingValue);
                $check = !preg_match("/{$targetingValueSting}/i", $contextValue);
                break;
            case "GREATER_THAN":
                $check = $contextValue > $targetingValue;
                break;
            case "LOWER_THAN":
                $check = $contextValue < $targetingValue;
                break;
            case "GREATER_THAN_OR_EQUALS":
                $check = $contextValue >= $targetingValue;
                break;
            case "LOWER_THAN_OR_EQUALS":
                $check = $contextValue <= $targetingValue;
                break;
            case "STARTS_WITH":
                $check = (bool)preg_match("/^{$targetingValue}/i", $contextValue);
                break;
            case "ENDS_WITH":
                $check = (bool)preg_match("/{$targetingValue}$/i", $contextValue);
                break;
            default:
                $check = false;
                break;
        }

        return $check;
    }
}
