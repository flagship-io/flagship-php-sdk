<?php

namespace Flagship\Utils;

use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{

    public function testIsKeyValid()
    {
        // Key is empty
        $this->assertFalse(Validator::isKeyValid(''));
        // Key is null
        $this->assertFalse(Validator::isKeyValid(null));
        // Key is not string
        $this->assertFalse(Validator::isKeyValid(44));
        $this->assertFalse(Validator::isKeyValid([]));

        //Key is valid
        $this->assertTrue(Validator::isKeyValid('ValidKey'));
    }

    public function testIsValueValid()
    {
        // Value is empty
        $this->assertFalse(Validator::isValueValid(''));

        // Value is null
        $this->assertFalse(Validator::isValueValid(null));

        //Value is not valid
        $this->assertFalse(Validator::isValueValid([]));

        //Test value is numeric
        $this->assertTrue(Validator::isValueValid(14));
        $this->assertTrue(Validator::isValueValid(14.5));

        //Test value is string
        $this->assertTrue(Validator::isValueValid("hello"));

        //Test value is boolean
        $this->assertTrue(Validator::isValueValid(true));
    }
}
