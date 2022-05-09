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
        $slug = "slug";
        $metadata = new FlagMetadata($campaignId, $variationGroupId, $variationId, $isReferenceId, $campaignType, $slug);

        $this->assertSame($metadata->getCampaignId(), $campaignId);
        $this->assertSame($metadata->getVariationGroupId(), $variationGroupId);
        $this->assertSame($metadata->getVariationId(), $variationId);
        $this->assertSame($metadata->isReference(), $isReferenceId);
        $this->assertSame($metadata->getCampaignType(), $campaignType);
        $this->assertSame($metadata->getSlug(), $slug);

        $metadataJson = json_encode([
            "campaignId" => $campaignId,
            "variationGroupId" => $variationGroupId,
            "variationId" => $variationId,
            "isReference" => $isReferenceId,
            "campaignType" => $campaignType,
            "slug" => $slug
        ]);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $metadataJson);

        $newSlug = "new_slug";
        $metadata->setSlug($newSlug);

        $this->assertSame($newSlug, $metadata->getSlug());

        $metadataJson = json_encode([
            "campaignId" => "",
            "variationGroupId" => "",
            "variationId" => "",
            "isReference" => false,
            "campaignType" => "",
            "slug"=>null
        ]);

        $this->assertJsonStringEqualsJsonString(json_encode(FlagMetadata::getEmpty()), $metadataJson);
    }
}
