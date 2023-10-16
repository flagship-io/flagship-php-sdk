<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;
use PHPUnit\Framework\TestCase;

class ModificationTest extends TestCase
{
    public function testModificationInstance()
    {

        $modification = new FlagDTO();

        $modificationValue = "value";
        $modification->setValue($modificationValue);

        $this->assertSame($modificationValue, $modification->getValue());

        $campaignId = 'sdfsf5sf45s2fg';
        $modification->setCampaignId($campaignId);

        $this->assertSame($campaignId, $modification->getCampaignId());

        $isReference = true;
        $modification->setIsReference($isReference);

        $this->assertSame($isReference, $modification->getIsReference());

        $modificationKey = "color";
        $modification->setKey($modificationKey);

        $this->assertSame($modificationKey, $modification->getKey());

        $variationGroupId = "et5ry2df8yuk54u";
        $modification->setVariationGroupId($variationGroupId);

        $this->assertSame($variationGroupId, $modification->getVariationGroupId());

        $variationId = "545re8tyu8o885xc21bv";
        $modification->setVariationId($variationId);

        $this->assertSame($variationId, $modification->getVariationId());

        $arrayToJson =  [
            FlagshipField::FIELD_KEY => $modification->getKey(),
            FlagshipField::FIELD_CAMPAIGN_ID => $modification->getCampaignId(),
            FlagshipField::FIELD_CAMPAIGN_NAME => $modification->getCampaignName(),
            FlagshipField::FIELD_VARIATION_GROUP_ID => $modification->getVariationGroupId(),
            FlagshipField::FIELD_VARIATION_GROUP_NAME => $modification->getVariationGroupName(),
            FlagshipField::FIELD_VARIATION_ID => $modification->getVariationId(),
            FlagshipField::FIELD_VARIATION_NAME => $modification->getVariationName(),
            FlagshipField::FIELD_IS_REFERENCE => $modification->getIsReference(),
            FlagshipField::FIELD_VALUE => $modification->getValue(),
            FlagshipField::FIELD_SLUG => $modification->getSlug()
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($arrayToJson), json_encode($modification));
    }
}
