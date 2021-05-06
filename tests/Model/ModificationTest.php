<?php

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;

class ModificationTest extends TestCase
{
    public function testModificationInstance()
    {

        $modification = new Modification();

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
    }
}
