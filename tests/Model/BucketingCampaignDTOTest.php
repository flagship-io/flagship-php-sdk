<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;

class BucketingCampaignDTOTest extends TestCase
{
    public function testConstructor()
    {
        $variationGroups = [];
        $dto = new BucketingCampaignDTO('campaign123', 'ab', $variationGroups);

        $this->assertSame('campaign123', $dto->getId());
        $this->assertSame('ab', $dto->getType());
        $this->assertSame($variationGroups, $dto->getVariationGroups());
    }

    public function testGettersAndSetters()
    {
        $dto = new BucketingCampaignDTO('id1', 'type1', []);

        $instance1 = $dto->setId('newId');
        $this->assertSame('newId', $dto->getId());
        $this->assertSame($dto, $instance1);

        $instance2 = $dto->setName('Campaign Name');
        $this->assertSame('Campaign Name', $dto->getName());
        $this->assertSame($dto, $instance2);

        $instance3 = $dto->setType('newType');
        $this->assertSame('newType', $dto->getType());
        $this->assertSame($dto, $instance3);

        $instance4 = $dto->setSlug('campaign-slug');
        $this->assertSame('campaign-slug', $dto->getSlug());
        $this->assertSame($dto, $instance4);

        $variationGroups = [
            new VariationGroupDTO('vg1', new TargetingDTO([]), [])
        ];
        $instance5 = $dto->setVariationGroups($variationGroups);
        $this->assertSame($variationGroups, $dto->getVariationGroups());
        $this->assertSame($dto, $instance5);
    }

    public function testFromArray()
    {
        $data = [
            FlagshipField::FIELD_ID => 'campaign123',
            FlagshipField::FIELD_CAMPAIGN_TYPE => 'ab',
            FlagshipField::FIELD_NANE => 'My Campaign',
            FlagshipField::FIELD_SLUG => 'my-campaign',
            FlagshipField::FIELD_VARIATION_GROUPS => []
        ];

        $dto = BucketingCampaignDTO::fromArray($data);

        $this->assertSame('campaign123', $dto->getId());
        $this->assertSame('ab', $dto->getType());
        $this->assertSame('My Campaign', $dto->getName());
        $this->assertSame('my-campaign', $dto->getSlug());
        $this->assertIsArray($dto->getVariationGroups());
    }

    public function testFromArrayWithInvalidTypes()
    {
        $data = [
            FlagshipField::FIELD_ID => 123,
            FlagshipField::FIELD_CAMPAIGN_TYPE => [],
            FlagshipField::FIELD_NANE => 456,
            FlagshipField::FIELD_SLUG => null
        ];

        $dto = BucketingCampaignDTO::fromArray($data);

        $this->assertSame('', $dto->getId());
        $this->assertSame('', $dto->getType());
        $this->assertNull($dto->getName());
        $this->assertNull($dto->getSlug());
    }

    public function testToArray()
    {
        $variationGroup = new VariationGroupDTO('vg1', new TargetingDTO([]), []);
        $dto = new BucketingCampaignDTO('campaign123', 'ab', [$variationGroup]);
        $dto->setName('Campaign Name');
        $dto->setSlug('campaign-slug');

        $array = $dto->toArray();

        $this->assertSame('campaign123', $array[FlagshipField::FIELD_ID]);
        $this->assertSame('ab', $array[FlagshipField::FIELD_CAMPAIGN_TYPE]);
        $this->assertIsArray($array[FlagshipField::FIELD_VARIATION_GROUPS]);
        $this->assertCount(1, $array[FlagshipField::FIELD_VARIATION_GROUPS]);
    }
}
