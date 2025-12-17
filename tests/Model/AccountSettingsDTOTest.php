<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;

class AccountSettingsDTOTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $dto = new AccountSettingsDTO();

        $this->assertNull($dto->getEnabledXPC());
        $this->assertNull($dto->getTroubleshooting());
        $this->assertNull($dto->getEaiCollectEnabled());
        $this->assertNull($dto->getEaiActivationEnabled());

        $instance1 = $dto->setEnabledXPC(true);
        $this->assertTrue($dto->getEnabledXPC());
        $this->assertSame($dto, $instance1);

        $troubleshooting = new TroubleshootingDTO('2025-01-01', '2025-12-31', 100.0, 'UTC');
        $instance2 = $dto->setTroubleshooting($troubleshooting);
        $this->assertSame($troubleshooting, $dto->getTroubleshooting());
        $this->assertSame($dto, $instance2);

        $instance3 = $dto->setEaiCollectEnabled(false);
        $this->assertFalse($dto->getEaiCollectEnabled());
        $this->assertSame($dto, $instance3);

        $instance4 = $dto->setEaiActivationEnabled(true);
        $this->assertTrue($dto->getEaiActivationEnabled());
        $this->assertSame($dto, $instance4);
    }

    public function testIsTroubleshootingEnabled()
    {
        $dto = new AccountSettingsDTO();
        $this->assertFalse($dto->isTroubleshootingEnabled());

        $troubleshooting = new TroubleshootingDTO('2025-01-01', '2025-12-31', 100.0, 'UTC');
        $dto->setTroubleshooting($troubleshooting);
        $this->assertTrue($dto->isTroubleshootingEnabled());

        $dto->setTroubleshooting(null);
        $this->assertFalse($dto->isTroubleshootingEnabled());
    }

    public function testFromArray()
    {
        $troubleshootingData = [
            FlagshipField::START_DATE => '2025-01-01',
            FlagshipField::END_DATE => '2025-12-31',
            FlagshipField::TRAFFIC => 75.5,
            FlagshipField::TIMEZONE => 'UTC'
        ];

        $data = [
            FlagshipField::ENABLED_XPC => true,
            FlagshipField::TROUBLESHOOTING => $troubleshootingData,
            FlagshipField::EAI_COLLECT_ENABLED => true,
            FlagshipField::EAI_ACTIVATION_ENABLED => false
        ];

        $dto = AccountSettingsDTO::fromArray($data);

        $this->assertTrue($dto->getEnabledXPC());
        $this->assertNotNull($dto->getTroubleshooting());
        $this->assertTrue($dto->getEaiCollectEnabled());
        $this->assertFalse($dto->getEaiActivationEnabled());
    }

    public function testFromArrayWithMissingFields()
    {
        $dto = AccountSettingsDTO::fromArray([]);

        $this->assertNull($dto->getEnabledXPC());
        $this->assertNull($dto->getTroubleshooting());
        $this->assertNull($dto->getEaiCollectEnabled());
        $this->assertNull($dto->getEaiActivationEnabled());
    }

    public function testFromArrayWithInvalidTypes()
    {
        $data = [
            FlagshipField::ENABLED_XPC => 'not a bool',
            FlagshipField::TROUBLESHOOTING => 'not an array',
            FlagshipField::EAI_COLLECT_ENABLED => 123,
            FlagshipField::EAI_ACTIVATION_ENABLED => []
        ];

        $dto = AccountSettingsDTO::fromArray($data);

        $this->assertNull($dto->getEnabledXPC());
        $this->assertNull($dto->getTroubleshooting());
        $this->assertNull($dto->getEaiCollectEnabled());
        $this->assertNull($dto->getEaiActivationEnabled());
    }

    public function testToArray()
    {
        $dto = new AccountSettingsDTO();
        $dto->setEnabledXPC(true);
        $dto->setEaiCollectEnabled(false);
        $dto->setEaiActivationEnabled(true);

        $troubleshooting = new TroubleshootingDTO('2025-01-01', '2025-12-31', 50.0, 'America/New_York');
        $dto->setTroubleshooting($troubleshooting);

        $array = $dto->toArray();

        $this->assertArrayHasKey(FlagshipField::ENABLED_XPC, $array);
        $this->assertTrue($array[FlagshipField::ENABLED_XPC]);

        $this->assertArrayHasKey(FlagshipField::TROUBLESHOOTING, $array);
        $this->assertIsArray($array[FlagshipField::TROUBLESHOOTING]);

        $this->assertArrayHasKey(FlagshipField::EAI_COLLECT_ENABLED, $array);
        $this->assertFalse($array[FlagshipField::EAI_COLLECT_ENABLED]);

        $this->assertArrayHasKey(FlagshipField::EAI_ACTIVATION_ENABLED, $array);
        $this->assertTrue($array[FlagshipField::EAI_ACTIVATION_ENABLED]);
    }

    public function testToArrayWithNullValues()
    {
        $dto = new AccountSettingsDTO();
        $array = $dto->toArray();

        $this->assertEmpty($array);
    }
}
