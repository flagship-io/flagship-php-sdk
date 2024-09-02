<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;
use PHPUnit\Framework\TestCase;

class FlagDTOTest extends TestCase
{
    public function testModificationInstance()
    {

        $flagDTO = new FlagDTO();

        $flagValue = "value";
        $flagDTO->setValue($flagValue);

        $this->assertSame($flagValue, $flagDTO->getValue());

        $campaignId = 'sdfsf5sf45s2fg';
        $flagDTO->setCampaignId($campaignId);

        $this->assertSame($campaignId, $flagDTO->getCampaignId());

        $isReference = true;
        $flagDTO->setIsReference($isReference);

        $this->assertSame($isReference, $flagDTO->getIsReference());

        $modificationKey = "color";
        $flagDTO->setKey($modificationKey);

        $this->assertSame($modificationKey, $flagDTO->getKey());

        $variationGroupId = "et5ry2df8yuk54u";
        $flagDTO->setVariationGroupId($variationGroupId);

        $this->assertSame($variationGroupId, $flagDTO->getVariationGroupId());

        $variationId = "545re8tyu8o885xc21bv";
        $flagDTO->setVariationId($variationId);

        $this->assertSame($variationId, $flagDTO->getVariationId());

        $arrayToJson = [
                        FlagshipField::FIELD_KEY                  => $flagDTO->getKey(),
                        FlagshipField::FIELD_CAMPAIGN_ID          => $flagDTO->getCampaignId(),
                        FlagshipField::FIELD_CAMPAIGN_NAME        => $flagDTO->getCampaignName(),
                        FlagshipField::FIELD_VARIATION_GROUP_ID   => $flagDTO->getVariationGroupId(),
                        FlagshipField::FIELD_VARIATION_GROUP_NAME => $flagDTO->getVariationGroupName(),
                        FlagshipField::FIELD_VARIATION_ID         => $flagDTO->getVariationId(),
                        FlagshipField::FIELD_VARIATION_NAME       => $flagDTO->getVariationName(),
                        FlagshipField::FIELD_IS_REFERENCE         => $flagDTO->getIsReference(),
                        FlagshipField::FIELD_VALUE                => $flagDTO->getValue(),
                        FlagshipField::FIELD_SLUG                 => $flagDTO->getSlug(),
                       ];

        $this->assertJsonStringEqualsJsonString(json_encode($arrayToJson), json_encode($flagDTO));
    }
}
