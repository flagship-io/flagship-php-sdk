<?php

namespace Flagship\Flag;

use PHPUnit\Framework\TestCase;

class FlagMetadataTest extends TestCase
{
    public function testConstruct()
    {
        $campaignId = "campaignID";
        $variationGroupId = "variationGroupID";
        $variationId = "variationId";
        $isReferenceId = true;
        $campaignType = "campaignType";
        $metadata = new FlagMetadata($campaignId, $variationGroupId, $variationId, $isReferenceId, $campaignType);

        $this->assertSame($metadata->getCampaignId(), $campaignId);
        $this->assertSame($metadata->getVariationGroupId(), $variationGroupId);
        $this->assertSame($metadata->getVariationId(), $variationId);
        $this->assertSame($metadata->isReference(), $isReferenceId);
        $this->assertSame($metadata->getCampaignType(), $campaignType);

        $metadataJson = json_encode([
            "campaignId" => $campaignId,
            "variationGroupId" => $variationGroupId,
            "variationId" => $variationId,
            "isReference" => $isReferenceId,
            "campaignType" => $campaignType
        ]);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $metadataJson);

        $metadataJson = json_encode([
            "campaignId" => "",
            "variationGroupId" => "",
            "variationId" => "",
            "isReference" => false,
            "campaignType" => ""
        ]);

        $this->assertJsonStringEqualsJsonString(json_encode(FlagMetadata::getEmpty()), $metadataJson);
    }
}
