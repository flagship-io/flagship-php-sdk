<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Config\BucketingConfig;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Utils\HttpClientInterface;
use Flagship\Utils\MurmurHash;
use Flagship\Visitor\VisitorAbstract;

class BucketingManager extends DecisionManagerAbstract
{
    const NB_MIN_CONTEXT_KEYS = 4;
    const INVALID_BUCKETING_FILE_URL = "Invalid bucketing file url";
    /**
     * @var MurmurHash
     */
    private $murmurHash;

    /**
     * @var BucketingConfig
     */
    protected $config;

    /**
     * @return BucketingConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param BucketingConfig $config
     * @return BucketingManager
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }






    public function __construct(HttpClientInterface $httpClient, BucketingConfig $config, MurmurHash $murmurHash)
    {
        parent::__construct($httpClient, $config);
        $this->murmurHash = $murmurHash;
    }

    protected function sendContext(VisitorAbstract $visitor)
    {
        if (count($visitor->getContext())<= self::NB_MIN_CONTEXT_KEYS){
            return;
        }
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
            $response = $this->httpClient->post($url, [], $postBody);
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

    protected  function getBucketingFile(){

        try {
            $this->httpClient->setTimeout($this->getConfig()->getTimeout() / 1000);
            $url = $this->getConfig()->getBucketingUrl();
            if (!$url){
                throw  new Exception(self::INVALID_BUCKETING_FILE_URL);
            }
            $response = $this->httpClient->get($url);
            return $response->getBody();
        } catch (Exception $exception) {
            $this->logError($this->getConfig(), $exception->getMessage(), [
                FlagshipConstant::TAG => __FUNCTION__
            ]);
        }
        return null;
    }
    /**
     * @inheritDoc
     */
    protected function getCampaigns(VisitorAbstract $visitor)
    {
        $bucketingCampaigns = $this->getBucketingFile();

        if (!$bucketingCampaigns) {
            return [];
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
                $campaign[FlagshipField::FIELD_CAMPAIGN_TYPE]
            );
            $visitorCampaigns = array_merge($visitorCampaigns, $currentCampaigns);
        }
        return $visitorCampaigns;
    }

    /**
     * @param $variationGroups
     * @param $campaignId
     * @param VisitorAbstract $visitor
     * @param string $campaignType
     * @return array
     */
    private function getVisitorCampaigns($variationGroups, $campaignId, VisitorAbstract $visitor, $campaignType)
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
                    FlagshipField::FIELD_VARIATION => $variations,
                    FlagshipField::FIELD_CAMPAIGN_TYPE => $campaignType
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
        $isMatching = false;
        foreach ($innerTargetings as $innerTargeting) {
            $key = $innerTargeting['key'];
            $operator = $innerTargeting["operator"];
            $targetingValue = $innerTargeting["value"];
            $visitorContext = $visitor->getContext();

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

    private function isANDListOperator($operator)
    {
        return in_array($operator, ['NOT_EQUALS', 'NOT_CONTAINS']);
    }

    private function testListOperatorLoop($operator, $contextValue, array $targetingValue, $initialCheck)
    {
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
    private function testListOperator($operator, $contextValue, array $targetingValue)
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
    private function testOperator($operator, $contextValue, $targetingValue)
    {

        if (is_array($targetingValue)) {
            return $this->testListOperator($operator, $contextValue, $targetingValue);
        }
        switch ($operator) {
            case "EQUALS":
                $check = $contextValue === $targetingValue;
                break;
            case "NOT_EQUALS":
                $check = $contextValue !== $targetingValue;
                break;
            case "CONTAINS":
                $check = strpos(strval($contextValue), strval($targetingValue)) !== false;
                break;
            case "NOT_CONTAINS":
                $check = strpos(strval($contextValue), strval($targetingValue)) === false;
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
