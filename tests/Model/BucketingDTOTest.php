<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;

class BucketingDTOTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $dto = new BucketingDTO();

        $this->assertNull($dto->getPanic());
        $this->assertNull($dto->getCampaigns());
        $this->assertNull($dto->getAccountSettings());

        $instance1 = $dto->setPanic(true);
        $this->assertTrue($dto->getPanic());
        $this->assertSame($dto, $instance1);

        $campaigns = [
            new BucketingCampaignDTO('c1', 'ab', [])
        ];
        $instance2 = $dto->setCampaigns($campaigns);
        $this->assertSame($campaigns, $dto->getCampaigns());
        $this->assertSame($dto, $instance2);

        $accountSettings = new AccountSettingsDTO();
        $instance3 = $dto->setAccountSettings($accountSettings);
        $this->assertSame($accountSettings, $dto->getAccountSettings());
        $this->assertSame($dto, $instance3);
    }

    public function testFromArray()
    {
        $data = [
            FlagshipField::FIELD_PANIC => true,
            FlagshipField::FIELD_CAMPAIGNS => [
                [
                    FlagshipField::FIELD_ID => 'c1',
                    FlagshipField::FIELD_CAMPAIGN_TYPE => 'ab',
                    FlagshipField::FIELD_VARIATION_GROUPS => []
                ]
            ],
            FlagshipField::ACCOUNT_SETTINGS => [
                FlagshipField::ENABLED_XPC => true
            ]
        ];

        $dto = BucketingDTO::fromArray($data);

        $this->assertTrue($dto->getPanic());
        $this->assertIsArray($dto->getCampaigns());
        $this->assertCount(1, $dto->getCampaigns());
        $this->assertNotNull($dto->getAccountSettings());
    }

    public function testFromArrayWithMissingFields()
    {
        $dto = BucketingDTO::fromArray([]);

        $this->assertNull($dto->getPanic());
        $this->assertNull($dto->getCampaigns());
        $this->assertNull($dto->getAccountSettings());
    }

    public function testToArray()
    {
        $dto = new BucketingDTO();
        $dto->setPanic(false);

        $campaign = new BucketingCampaignDTO('c1', 'ab', []);
        $dto->setCampaigns([$campaign]);

        $accountSettings = new AccountSettingsDTO();
        $accountSettings->setEnabledXPC(true);
        $dto->setAccountSettings($accountSettings);

        $array = $dto->toArray();

        $this->assertFalse($array[FlagshipField::FIELD_PANIC]);
        $this->assertIsArray($array[FlagshipField::FIELD_CAMPAIGNS]);
        $this->assertIsArray($array[FlagshipField::ACCOUNT_SETTINGS]);
    }

    public function testToArrayWithNullValues()
    {
        $dto = new BucketingDTO();
        $array = $dto->toArray();

        $this->assertArrayNotHasKey(FlagshipField::FIELD_PANIC, $array);
    }
}
