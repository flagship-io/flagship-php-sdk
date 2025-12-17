<?php

namespace Flagship\Traits;

use Flagship\Config\BucketingConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class ValidatorTraitTest extends TestCase
{

    /**
     * @throws ReflectionException
     */
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

    /**
     * @throws ReflectionException
     */
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


        $config = new BucketingConfig("http://127.0.0.1:3000");
        $config->setLogManager($logManagerStub);
        $value = "linux";
        $check = $checkFlagshipContext->invokeArgs($validatorTraitMock, ['item', $value, $config]);
        $this->assertNull($check);

        $sdk = FlagshipConstant::FLAGSHIP_SDK;
        $logManagerStub->expects($this->once())->method('error')->with(
            sprintf(
                FlagshipConstant::FLAGSHIP_PREDEFINED_CONTEXT_ERROR,
                "sdk_osName",
                "string"
            )
        );
        $value = 1;
        $check = $checkFlagshipContext->invokeArgs(
            $validatorTraitMock,
            [
             FlagshipContext::OS_NAME,
             $value,
             $config,
            ]
        );
        $this->assertFalse($check);

        $value = "mac";
        $check = $checkFlagshipContext->invokeArgs(
            $validatorTraitMock,
            [
             FlagshipContext::OS_NAME,
             $value,
             $config,
            ]
        );
        $this->assertTrue($check);
    }

    /**
     * @throws ReflectionException
     */
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
}
