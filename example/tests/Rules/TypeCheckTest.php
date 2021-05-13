<?php

namespace Rules;

use App\Rules\TypeCheck;
use TestCase;

class TypeCheckTest extends TestCase
{
    public function testPassesBoolean()
    {
        $type = "bool";
        $typeChecker = new TypeCheck($type);
        $attribute = 'attribute';

        $inputData = true;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = false;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = 0;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = 1;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = "false";
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = "abc";
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertFalse($value);

        $inputData = "2";
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertFalse($value);

        $inputData = 2;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertFalse($value);

        $message = $typeChecker->message();

        $this->assertSame('The ' . $attribute . ' is not ' . $type, $message);
    }

    public function testPassesNumeric()
    {
        $type = "double";
        $typeChecker = new TypeCheck($type);
        $attribute = 'attribute';

        $inputData = true;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertFalse($value);

        $inputData = false;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertFalse($value);

        $inputData = "abc";
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertFalse($value);

        $inputData = 1;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = 1.0;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = "1.0";
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);

        $type = "long";
        $typeChecker = new TypeCheck($type);
        $inputData = 1;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);

        $type = "float";
        $typeChecker = new TypeCheck($type);
        $inputData = 1;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);

        $type = "int";
        $typeChecker = new TypeCheck($type);
        $inputData = 1;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);

        $type = "integer";
        $typeChecker = new TypeCheck($type);
        $inputData = 1;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);
    }

    public function testPassesString()
    {
        $type = "string";
        $typeChecker = new TypeCheck($type);
        $attribute = 'attribute';

        $inputData = "abc";
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertTrue($value);

        $inputData = true;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertFalse($value);

        $inputData = false;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertFalse($value);

        $inputData = 1;
        $value = $typeChecker->passes($attribute, $inputData);
        $this->assertFalse($value);
    }
}
