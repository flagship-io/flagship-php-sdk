<?php

namespace Flagship\Utils;

use PHPUnit\Framework\TestCase;

class MurmurHashTest extends TestCase
{

    public function testMurmurHash3Int32()
    {
        $murmurHash = new MurmurHash();
        $hash = $murmurHash->murmurHash3Int32('123visitor_1');
        $this->assertSame(3635969351, $hash);
        $hash = $murmurHash->murmurHash3Int32('9273BKSDJtoto123456');
        $this->assertSame(2207745127, $hash);
        $hash = $murmurHash->murmurHash3Int32('vgidflagship@abtast.com');
        $this->assertSame(1551214225, $hash);
        $hash = $murmurHash->murmurHash3Int32('vgidéééàëééééé');
        $this->assertSame(1846876870, $hash);
    }
}
