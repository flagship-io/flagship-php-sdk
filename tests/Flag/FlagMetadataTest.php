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
        $campaignName = "campaignName";
        $variationName = "variationName";
        $variationGroupName = "variationGroupName";
        $slug = "slug";
        $metadata = new FSFlagMetadata(
            $campaignId,
            $variationGroupId,
            $variationId,
            $isReferenceId,
            $campaignType,
            $slug,
            $campaignName,
            $variationGroupName,
            $variationName
        );

        $this->assertSame($metadata->getCampaignId(), $campaignId);
        $this->assertSame($metadata->getVariationGroupId(), $variationGroupId);
        $this->assertSame($metadata->getVariationId(), $variationId);
        $this->assertSame($metadata->isReference(), $isReferenceId);
        $this->assertSame($metadata->getCampaignType(), $campaignType);
        $this->assertSame($metadata->getSlug(), $slug);
        $this->assertSame($metadata->getVariationGroupName(), $variationGroupName);
        $this->assertSame($metadata->getCampaignName(), $campaignName);
        $this->assertSame($metadata->getVariationName(), $variationName);

        $metadataJson = json_encode([
                                     "campaignId"         => $campaignId,
                                     "campaignName"       => $campaignName,
                                     "variationGroupId"   => $variationGroupId,
                                     "variationGroupName" => $variationGroupName,
                                     "variationId"        => $variationId,
                                     "variationName"      => $variationName,
                                     "isReference"        => $isReferenceId,
                                     "campaignType"       => $campaignType,
                                     "slug"               => $slug,
                                    ]);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $metadataJson);

        $metadataJson = json_encode([
                                     "campaignId"         => "",
                                     "campaignName"       => "",
                                     "variationGroupId"   => "",
                                     "variationGroupName" => '',
                                     "variationId"        => "",
                                     "variationName"      => "",
                                     "isReference"        => false,
                                     "campaignType"       => "",
                                     "slug"               => "",
                                    ]);

        $this->assertJsonStringEqualsJsonString(json_encode(FSFlagMetadata::getEmpty()), $metadataJson);
    }
}
