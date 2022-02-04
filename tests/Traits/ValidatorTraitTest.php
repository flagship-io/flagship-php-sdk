<?php

namespace Flagship\Traits;

use Flagship\Config\BucketingConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;

class ValidatorTraitTest extends TestCase
{
    public function testIsValueValid()
    {
        $validatorTraitMock = $this->getMockForTrait(
            'Flagship\Traits\ValidatorTrait',
            [],
            "",
            false,
            true,
            true
        );
        $isKeyValid = Utils::getMethod($validatorTraitMock, "isKeyValid");
        // Key is empty
        $this->assertFalse($isKeyValid->invokeArgs($validatorTraitMock, ['']));
        // Key is null
        $this->assertFalse($isKeyValid->invokeArgs($validatorTraitMock, [null]));
        // Key is not string
        $this->assertFalse($isKeyValid->invokeArgs($validatorTraitMock, [44]));
        $this->assertFalse($isKeyValid->invokeArgs($validatorTraitMock, [[]]));

        //Key is valid
        $this->assertTrue($isKeyValid->invokeArgs($validatorTraitMock, ['validKey']));
    }


    public function testIsKeyValid()
    {
        $validatorTraitMock = $this->getMockForTrait(
            'Flagship\Traits\ValidatorTrait',
            [],
            "",
            false,
            true,
            true
        );

        $isValueValid = Utils::getMethod($validatorTraitMock, "isValueValid");
        // Value is empty
        $this->assertTrue($isValueValid->invokeArgs($validatorTraitMock, ['']));

        // Value is null
        $this->assertTrue($isValueValid->invokeArgs($validatorTraitMock, [null]));

        //Value is not valid
        $this->assertFalse($isValueValid->invokeArgs($validatorTraitMock, [[]]));

        //Test value is numeric
        $this->assertTrue($isValueValid->invokeArgs($validatorTraitMock, [14]));
        $this->assertTrue($isValueValid->invokeArgs($validatorTraitMock, [14.5]));

        //Test value is string
        $this->assertTrue($isValueValid->invokeArgs($validatorTraitMock, ['abc']));

        //Test value is boolean
        $this->assertTrue($isValueValid->invokeArgs($validatorTraitMock, [true]));

        //Test value is boolean
        $this->assertTrue($isValueValid->invokeArgs($validatorTraitMock, [false]));
    }

    public function testCheckType()
    {
        $validatorTraitMock = $this->getMockForTrait(
            'Flagship\Traits\ValidatorTrait',
            [],
            "",
            false,
            true,
            true
        );

        $checkType = Utils::getMethod($validatorTraitMock, "checkType");

        // Value is empty
        $this->assertTrue($checkType->invokeArgs($validatorTraitMock, ['bool', true]));
        $this->assertTrue($checkType->invokeArgs($validatorTraitMock, ['bool', false]));
        $this->assertFalse($checkType->invokeArgs($validatorTraitMock, ['bool', "abc"]));

        $this->assertTrue($checkType->invokeArgs($validatorTraitMock, ['float', 1.5]));
        $this->assertTrue($checkType->invokeArgs($validatorTraitMock, ['double', 1.5]));
        $this->assertTrue($checkType->invokeArgs($validatorTraitMock, ['integer', 1]));
        $this->assertFalse($checkType->invokeArgs($validatorTraitMock, ['integer', "abc"]));

        $this->assertTrue($checkType->invokeArgs($validatorTraitMock, ['string', "abc"]));
    }

    public function testCheckFlagshipContext()
    {
        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $validatorTraitMock = $this->getMockForTrait(
            'Flagship\Traits\ValidatorTrait',
            [],
            "",
            false,
            true,
            true
        );

        $checkFlagshipContext = Utils::getMethod($validatorTraitMock, "checkFlagshipContext");
        $config = new BucketingConfig();
        $config->setLogManager($logManagerStub);
        $value = "linux";
        $check = $checkFlagshipContext->invokeArgs($validatorTraitMock, ['item',$value, $config]);
        $this->assertNull($check);

        $sdk = FlagshipConstant::FLAGSHIP_SDK;
        $logManagerStub->expects($this->once())->method('error')
            ->with("[$sdk] " .
                sprintf(
                    FlagshipConstant::FLAGSHIP_PREDEFINED_CONTEXT_ERROR,
                    "sdk_osName",
                    "string"
                ));
        $value = 1;
        $check = $checkFlagshipContext->invokeArgs(
            $validatorTraitMock,
            [FlagshipContext::OS_NAME,$value, $config]
        );
        $this->assertFalse($check);

        $value = "mac";
        $check = $checkFlagshipContext->invokeArgs(
            $validatorTraitMock,
            [FlagshipContext::OS_NAME,$value, $config]
        );
        $this->assertTrue($check);
    }

    public function testIsJsonObject()
    {
        $validatorTraitMock = $this->getMockForTrait(
            'Flagship\Traits\ValidatorTrait',
            [],
            "",
            false,
            true,
            true
        );

        $isJsonObject = Utils::getMethod($validatorTraitMock, "isJsonObject");

        $this->assertFalse($isJsonObject->invokeArgs($validatorTraitMock, ["item"]));
        $this->assertTrue($isJsonObject->invokeArgs($validatorTraitMock, ["{}"]));
        $this->assertFalse($isJsonObject->invokeArgs($validatorTraitMock, ["[]"]));
    }

    public function testIsNumeric()
    {
        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $validatorTraitMock = $this->getMockForTrait(
            'Flagship\Traits\ValidatorTrait',
            [],
            "",
            false,
            true,
            true
        );
        $itemName = "test";

        $isNumeric = Utils::getMethod($validatorTraitMock, "isNumeric");
        $config = new BucketingConfig();
        $config->setLogManager($logManagerStub);
        $this->assertTrue($isNumeric->invokeArgs($validatorTraitMock, [1, $itemName, $config]));

        $sdk = FlagshipConstant::FLAGSHIP_SDK;
        $logManagerStub->expects($this->once())->method('error')
            ->with("[$sdk] " .
                sprintf(FlagshipConstant::TYPE_ERROR, $itemName, 'numeric'));

        $this->assertFalse($isNumeric->invokeArgs($validatorTraitMock, ["abc", $itemName, $config]));
    }
}
