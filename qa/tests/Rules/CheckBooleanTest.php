<?php

namespace Rules;

use App\Rules\CheckBoolean;
use TestCase;

class CheckBooleanTest extends TestCase
{
    public function testPasses()
    {
        $checkBoolean = new CheckBoolean();
        $attribute = 'attribute';
        $inputData = true;
        $value = $checkBoolean->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = false;
        $value = $checkBoolean->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = 0;
        $value = $checkBoolean->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = 1;
        $value = $checkBoolean->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = "false";
        $value = $checkBoolean->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = "abc";
        $value = $checkBoolean->passes($attribute, $inputData);
        $this->assertFalse($value);

        $inputData = "2";
        $value = $checkBoolean->passes($attribute, $inputData);
        $this->assertFalse($value);

        $inputData = 2;
        $value = $checkBoolean->passes($attribute, $inputData);
        $this->assertFalse($value);

        $message = $checkBoolean->message();

        $this->assertSame('The ' . $attribute . ' is not ' . 'bool', $message);
    }
}
