<?php

namespace Flagship\Decision;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Model\Modification;
use Flagship\Utils\HttpClientInterface;
use Flagship\Utils\MurmurHash;
use Flagship\Visitor\VisitorAbstract;

class BucketingManager extends DecisionManagerAbstract
{

    /**
     * @var MurmurHash
     */
    private $murmurHash;

    public function __construct(HttpClientInterface $httpClient, MurmurHash $murmurHash)
    {
        parent::__construct($httpClient);
        $this->murmurHash = $murmurHash;
    }

    /**
     * @inheritDoc
     */
    public function getCampaignModifications(VisitorAbstract $visitor)
    {
        return $this->getBucketingCampaigns($visitor);
    }

    private function getBucketingCampaigns(VisitorAbstract $visitor)
    {
        $bucketingFile = __DIR__ . "/../../bucketing.json";
        if (!file_exists($bucketingFile)) {
            return null;
        }
        $bucketingCampaigns = file_get_contents($bucketingFile);

        if (!$bucketingCampaigns) {
            return null;
        }

        $bucketingCampaigns = json_decode($bucketingCampaigns, true);

        if (!isset($bucketingCampaigns[FlagshipField::FIELD_CAMPAIGNS])) {
            return null;
        }


        $campaigns = $bucketingCampaigns[FlagshipField::FIELD_CAMPAIGNS];

        $visitorCampaigns = [];

        foreach ($campaigns as $campaign) {
            if (!isset($campaign[FlagshipField::FIELD_VARIATION_GROUPS])) {
                continue;
            }
            $variationGroups = $campaign[FlagshipField::FIELD_VARIATION_GROUPS];
            foreach ($variationGroups as $variationGroup) {
                $check = $this->isMatchTargeting($variationGroup, $visitor);
                if ($check) {
                    $variations = $this->getVariation(
                        $variationGroup,
                        $visitor->getVisitorId()
                    );
                    $visitorCampaigns[] = [
                        FlagshipField::FIELD_ID => $campaign[FlagshipField::FIELD_ID],
                        FlagshipField::FIELD_VARIATION_GROUP_ID => $variationGroup[FlagshipField::FIELD_ID],
                        FlagshipField::FIELD_VARIATION => $variations
                    ];
                    continue 2;
                }
            }
        }

        return [
            "visitorId" => $visitor->getVisitorId(),
            FlagshipField::FIELD_CAMPAIGNS => $visitorCampaigns
        ];
    }

    private function getVariation($variationGroup, $visitorId)
    {
        $groupVariationId = $variationGroup[FlagshipField::FIELD_ID];
        $hash = $this->murmurHash->murmurHash3Int32($groupVariationId . $visitorId);
        $hashAllocation = $hash % 100;
        $variations = $variationGroup["variations"];
        $totalAllocation = 0;
        foreach ($variations as $variation) {
            $totalAllocation += $variation['allocation'];
            if ($hashAllocation < $totalAllocation) {
                return $variation;
            }
        }
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
            if (!isset($targetingGroup['targetings'])) {
                continue;
            }

            $innerTargetings = $targetingGroup['targetings'];

            $check = $this->checkAndTargeting($innerTargetings, $visitor);
            if ($check) {
                return true;
            }
        }

        return false;
    }

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
                $check = preg_match("/{$targetingValueSting}/i", $contextValue);
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
                $check = preg_match("/^{$targetingValue}/i", $contextValue);
                break;
            case "ENDS_WITH":
                $check = preg_match("/{$targetingValue}$/i", $contextValue);
                break;
            default:
                $check = false;
                break;
        }

        return $check;
    }
}
