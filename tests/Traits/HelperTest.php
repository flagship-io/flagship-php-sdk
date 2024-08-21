<?php

namespace Flagship\Traits;

use DateTime;
use Flagship\Traits\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    use Helper;

    public function testGetNow()
    {
        $now = $this->getNow();
        $this->assertIsFloat($now);
    }

    public function testGetCurrentDateTime()
    {
        $dateTime = $this->getCurrentDateTime();
        $this->assertInstanceOf(DateTime::class, $dateTime);
    }

    public function testValueToHex()
    {
        $value = ["v" => "value2"];
        $expectedHex = "7b2276223a2276616c756532227d";
        $this->assertSame($expectedHex, $this->valueToHex($value));
    }

    public function testArraysAreEqual()
    {
        $array1 = ["key1" => "value1", "key2" => "value2"];
        $array2 = ["key1" => "value1", "key2" => "value2"];
        $this->assertTrue($this->arraysAreEqual($array1, $array2));

        $array1 = ["key1" => "value1", "key2" => "value2"];
        $array2 = ["key1" => "value1", "key2" => "differentValue"];
        $this->assertFalse($this->arraysAreEqual($array1, $array2));

        $array1 = ["key1" => "value1", "key2" => ["subKey" => "subValue"]];
        $array2 = ["key1" => "value1", "key2" => ["subKey" => "subValue"]];
        $this->assertTrue($this->arraysAreEqual($array1, $array2));

        $array1 = ["key1" => "value1", "key2" => ["subKey" => "subValue"]];
        $array2 = ["key1" => "value1", "key2" => ["subKey" => "differentSubValue"]];
        $this->assertFalse($this->arraysAreEqual($array1, $array2));

        $array1 = ["key1" => "value1", "key2" => ["subKey" => "subValue"]];
        $array2 = ["key1" => "value1", "key2" => ["subKey" => "subValue", "extraKey" => "extraValue"]];
        $this->assertFalse($this->arraysAreEqual($array1, $array2));

        $array1 = ["key1" => "value1", "key2" => ["subKey2" => "subValue"]];
        $array2 = ["key1" => "value1", "key2" => ["subKey" => "subValue"]];
        $this->assertFalse($this->arraysAreEqual($array1, $array2));
    }
}