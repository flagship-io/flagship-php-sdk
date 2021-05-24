<?php

namespace Flagship\Traits;

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
        $this->assertFalse($isValueValid->invokeArgs($validatorTraitMock, ['']));

        // Value is null
        $this->assertFalse($isValueValid->invokeArgs($validatorTraitMock, [null]));

        //Value is not valid
        $this->assertFalse($isValueValid->invokeArgs($validatorTraitMock, [[]]));

        //Test value is numeric
        $this->assertTrue($isValueValid->invokeArgs($validatorTraitMock, [14]));
        $this->assertTrue($isValueValid->invokeArgs($validatorTraitMock, [14.5]));

        //Test value is string
        $this->assertTrue($isValueValid->invokeArgs($validatorTraitMock, ['abc']));

        //Test value is boolean
        $this->assertTrue($isValueValid->invokeArgs($validatorTraitMock, [true]));
    }
}
