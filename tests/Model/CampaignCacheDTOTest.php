<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;
use Flagship\Visitor\StrategyAbstract;

class CampaignCacheDTOTest extends TestCase
{
    public function testConstructor()
    {
        $flags = new ModificationsDTO('ab', ['key1' => 'value1']);
        $dto = new CampaignCacheDTO('c1', 'vg1', 'v1', $flags);

        $this->assertSame('c1', $dto->getCampaignId());
        $this->assertSame('vg1', $dto->getVariationGroupId());
        $this->assertSame('v1', $dto->getVariationId());
        $this->assertSame($flags, $dto->getFlags());
    }

    public function testGettersAndSetters()
    {
        $flags = new ModificationsDTO('ab', []);
        $dto = new CampaignCacheDTO('c1', 'vg1', 'v1', $flags);

        $instance1 = $dto->setCampaignId('c2');
        $this->assertSame('c2', $dto->getCampaignId());
        $this->assertSame($dto, $instance1);

        $instance2 = $dto->setVariationGroupId('vg2');
        $this->assertSame('vg2', $dto->getVariationGroupId());
        $this->assertSame($dto, $instance2);

        $instance3 = $dto->setVariationId('v2');
        $this->assertSame('v2', $dto->getVariationId());
        $this->assertSame($dto, $instance3);

        $instance4 = $dto->setType('ab');
        $this->assertSame('ab', $dto->getType());
        $this->assertSame($dto, $instance4);

        $instance5 = $dto->setSlug('campaign-slug');
        $this->assertSame('campaign-slug', $dto->getSlug());
        $this->assertSame($dto, $instance5);

        $instance6 = $dto->setName('Campaign Name');
        $this->assertSame('Campaign Name', $dto->getName());
        $this->assertSame($dto, $instance6);

        $instance7 = $dto->setIsReference(true);
        $this->assertTrue($dto->getIsReference());
        $this->assertSame($dto, $instance7);

        $instance8 = $dto->setActivated(false);
        $this->assertFalse($dto->getActivated());
        $this->assertSame($dto, $instance8);

        $newFlags = new ModificationsDTO('toggle', ['key2' => 'value2']);
        $instance9 = $dto->setFlags($newFlags);
        $this->assertSame($newFlags, $dto->getFlags());
        $this->assertSame($dto, $instance9);
    }

    public function testFromArray()
    {
        $data = [
            StrategyAbstract::CAMPAIGN_ID => 'c1',
            StrategyAbstract::VARIATION_GROUP_ID => 'vg1',
            StrategyAbstract::VARIATION_ID => 'v1',
            FlagshipField::FIELD_CAMPAIGN_TYPE => 'ab',
            FlagshipField::FIELD_SLUG => 'slug',
            FlagshipField::FIELD_CAMPAIGN_NAME => 'Campaign',
            FlagshipField::FIELD_IS_REFERENCE => true,
            FlagshipField::FIELD_ACTIVATED => true,
            FlagshipField::FIELD_FLAGS => [
                FlagshipField::FIELD_CAMPAIGN_TYPE => 'ab',
                FlagshipField::FIELD_VALUE => ['key' => 'value']
            ]
        ];

        $dto = CampaignCacheDTO::fromArray($data);

        $this->assertSame('c1', $dto->getCampaignId());
        $this->assertSame('vg1', $dto->getVariationGroupId());
        $this->assertSame('v1', $dto->getVariationId());
        $this->assertSame('ab', $dto->getType());
        $this->assertSame('Campaign', $dto->getName());
        $this->assertTrue($dto->getIsReference());
        $this->assertTrue($dto->getActivated());
        $this->assertInstanceOf(ModificationsDTO::class, $dto->getFlags());
    }

    public function testFromArrayWithMissingFields()
    {
        $dto = CampaignCacheDTO::fromArray([]);

        $this->assertSame('', $dto->getCampaignId());
        $this->assertSame('', $dto->getVariationGroupId());
        $this->assertSame('', $dto->getVariationId());
        $this->assertInstanceOf(ModificationsDTO::class, $dto->getFlags());
    }

    public function testFromArrayWithInvalidTypes()
    {
        $data = [
            StrategyAbstract::CAMPAIGN_ID => 123,
            StrategyAbstract::VARIATION_GROUP_ID => [],
            StrategyAbstract::VARIATION_ID => null,
            FlagshipField::FIELD_CAMPAIGN_TYPE => 456,
            FlagshipField::FIELD_IS_REFERENCE => 'true',
            FlagshipField::FIELD_ACTIVATED => 1
        ];

        $dto = CampaignCacheDTO::fromArray($data);

        $this->assertSame('', $dto->getCampaignId());
        $this->assertNull($dto->getType());
        $this->assertNull($dto->getIsReference());
        $this->assertNull($dto->getActivated());
    }
}
