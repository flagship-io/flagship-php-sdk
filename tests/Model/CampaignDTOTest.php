<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;

class CampaignDTOTest extends TestCase
{
    public function testConstructor()
    {
        $variation = new VariationDTO('v1', new ModificationsDTO('ab', []));
        $dto = new CampaignDTO('c1', 'vg1', $variation);

        $this->assertSame('c1', $dto->getId());
        $this->assertSame('vg1', $dto->getVariationGroupId());
        $this->assertSame($variation, $dto->getVariation());
    }

    public function testGettersAndSetters()
    {
        $variation = new VariationDTO('v1', new ModificationsDTO('ab', []));
        $dto = new CampaignDTO('c1', 'vg1', $variation);

        $instance1 = $dto->setId('c2');
        $this->assertSame('c2', $dto->getId());
        $this->assertSame($dto, $instance1);

        $instance2 = $dto->setName('Campaign Name');
        $this->assertSame('Campaign Name', $dto->getName());
        $this->assertSame($dto, $instance2);

        $instance3 = $dto->setSlug('campaign-slug');
        $this->assertSame('campaign-slug', $dto->getSlug());
        $this->assertSame($dto, $instance3);

        $instance4 = $dto->setVariationGroupId('vg2');
        $this->assertSame('vg2', $dto->getVariationGroupId());
        $this->assertSame($dto, $instance4);

        $instance5 = $dto->setVariationGroupName('VG Name');
        $this->assertSame('VG Name', $dto->getVariationGroupName());
        $this->assertSame($dto, $instance5);

        $newVariation = new VariationDTO('v2', new ModificationsDTO('toggle', []));
        $instance6 = $dto->setVariation($newVariation);
        $this->assertSame($newVariation, $dto->getVariation());
        $this->assertSame($dto, $instance6);

        $instance7 = $dto->setType('ab');
        $this->assertSame('ab', $dto->getType());
        $this->assertSame($dto, $instance7);
    }

    public function testFromArray()
    {
        $data = [
            FlagshipField::FIELD_ID => 'c1',
            FlagshipField::FIELD_VARIATION_GROUP_ID => 'vg1',
            FlagshipField::FIELD_NANE => 'Campaign Name',
            FlagshipField::FIELD_SLUG => 'campaign-slug',
            FlagshipField::FIELD_VARIATION_GROUP_NAME => 'VG Name',
            FlagshipField::FIELD_CAMPAIGN_TYPE => 'ab',
            FlagshipField::FIELD_VARIATION => [
                FlagshipField::FIELD_ID => 'v1',
                FlagshipField::FIELD_MODIFICATIONS => [
                    FlagshipField::FIELD_CAMPAIGN_TYPE => 'ab',
                    FlagshipField::FIELD_VALUE => []
                ]
            ]
        ];

        $dto = CampaignDTO::fromArray($data);

        $this->assertSame('c1', $dto->getId());
        $this->assertSame('vg1', $dto->getVariationGroupId());
        $this->assertSame('Campaign Name', $dto->getName());
        $this->assertSame('campaign-slug', $dto->getSlug());
        $this->assertSame('VG Name', $dto->getVariationGroupName());
        $this->assertSame('ab', $dto->getType());
        $this->assertInstanceOf(VariationDTO::class, $dto->getVariation());
    }

    public function testFromArrayWithMissingFields()
    {
        $dto = CampaignDTO::fromArray([]);

        $this->assertSame('', $dto->getId());
        $this->assertSame('', $dto->getVariationGroupId());
        $this->assertNull($dto->getName());
        $this->assertNull($dto->getSlug());
        $this->assertNull($dto->getVariationGroupName());
        $this->assertNull($dto->getType());
        $this->assertInstanceOf(VariationDTO::class, $dto->getVariation());
    }

    public function testToArray()
    {
        $variation = new VariationDTO('v1', new ModificationsDTO('ab', ['key' => 'value']));
        $dto = new CampaignDTO('c1', 'vg1', $variation);
        $dto->setName('Campaign Name');
        $dto->setSlug('slug');
        $dto->setVariationGroupName('VG Name');
        $dto->setType('ab');

        $array = $dto->toArray();

        $this->assertSame('c1', $array[FlagshipField::FIELD_ID]);
        $this->assertSame('Campaign Name', $array[FlagshipField::FIELD_NANE]);
        $this->assertSame('slug', $array[FlagshipField::FIELD_SLUG]);
        $this->assertSame('vg1', $array[FlagshipField::FIELD_VARIATION_GROUP_ID]);
        $this->assertSame('VG Name', $array[FlagshipField::FIELD_VARIATION_GROUP_NAME]);
        $this->assertSame('ab', $array[FlagshipField::FIELD_CAMPAIGN_TYPE]);
        $this->assertIsArray($array[FlagshipField::FIELD_VARIATION]);
    }
}
