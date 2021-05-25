<?php

namespace Flagship\Traits;

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
        // Key is empty
        $this->assertFalse($validatorTraitMock->isKeyValid(''));
        // Key is null
        $this->assertFalse($validatorTraitMock->isKeyValid(null));
        // Key is not string
        $this->assertFalse($validatorTraitMock->isKeyValid(44));
        $this->assertFalse($validatorTraitMock->isKeyValid([]));

        //Key is valid
        $this->assertTrue($validatorTraitMock->isKeyValid('ValidKey'));
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
        // Value is empty
        $this->assertFalse($validatorTraitMock->isValueValid(''));

        // Value is null
        $this->assertFalse($validatorTraitMock->isValueValid(null));

        //Value is not valid
        $this->assertFalse($validatorTraitMock->isValueValid([]));

        //Test value is numeric
        $this->assertTrue($validatorTraitMock->isValueValid(14));
        $this->assertTrue($validatorTraitMock->isValueValid(14.5));

        //Test value is string
        $this->assertTrue($validatorTraitMock->isValueValid("hello"));

        //Test value is boolean
        $this->assertTrue($validatorTraitMock->isValueValid(true));
        //Test value is boolean
        $this->assertTrue($validatorTraitMock->isValueValid(false));
    }
}
