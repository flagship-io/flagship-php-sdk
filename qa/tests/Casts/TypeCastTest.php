<?php

namespace Casts;

use App\Casts\TypeCast;
use TestCase;

class TypeCastTest extends TestCase
{
    public function testCastToTypeBoolean()
    {
        $casType = new TypeCast();

        $value = $casType->castToType(true, 'bool');
        $this->assertIsBool($value);
        $this->assertTrue($value);

        $value = $casType->castToType(false, 'bool');
        $this->assertIsBool($value);
        $this->assertFalse($value);

        $value = $casType->castToType("false", 'bool');
        $this->assertIsBool($value);
        $this->assertFalse($value);

        $value = $casType->castToType("true", 'bool');
        $this->assertIsBool($value);
        $this->assertTrue($value);

        $value = $casType->castToType(1, 'bool');
        $this->assertIsBool($value);
        $this->assertTrue($value);

        $value = $casType->castToType(0, 'bool');
        $this->assertIsBool($value);
        $this->assertFalse($value);

        $value = $casType->castToType("0", 'bool');
        $this->assertIsBool($value);
        $this->assertFalse($value);
    }

    public function testCastToTypeNumber()
    {
        $casType = new TypeCast();

        $dataInput = "144";
        $value = $casType->castToType($dataInput, 'double');
        $this->assertIsFloat($value);
        $this->assertEquals((float) $dataInput, $value);

        $value = $casType->castToType($dataInput, 'long');
        $this->assertIsFloat($value);
        $this->assertEquals((float) $dataInput, $value);

        $value = $casType->castToType($dataInput, 'float');
        $this->assertIsFloat($value);
        $this->assertEquals((float) $dataInput, $value);

        $value = $casType->castToType($dataInput, 'int');
        $this->assertIsInt($value);
        $this->assertEquals((float) $dataInput, $value);

        $value = $casType->castToType($dataInput, 'integer');
        $this->assertIsInt($value);
        $this->assertEquals((float) $dataInput, $value);
    }

    public function testCastToTypeString()
    {
        $casType = new TypeCast();

        $dataInput = "144";
        $value = $casType->castToType($dataInput, 'string');
        $this->assertIsString($value);
        $this->assertEquals($dataInput, $value);
    }

}
