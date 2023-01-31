<?php

namespace Flagship\Utils;

class MurmurHash
{
    /**
     * @param mixed $k1
     * @param mixed $constant
     * @return int
     */
    private function multiply($k1, $constant)
    {
        return ((($k1 & 0xffff) * $constant) +
                ((($k1 >= 0 ? $k1 >> 16 :
                            (($k1 & 0x7fffffff) >> 16) | 0x8000) * $constant & 0xffff) << 16)) & 0xffffffff;
    }

    /**
     * @param mixed $k1
     * @return int
     */
    private function rotateLeft($k1)
    {
        return $k1 << 15 | ($k1 >= 0 ? $k1 >> 17 : (($k1 & 0x7fffffff) >> 17) | 0x4000);
    }

    /**
     * @param mixed $value
     * @param mixed $numberDecalBit
     * @param mixed $constant
     * @return int
     */
    private function rotateRight($value, $numberDecalBit, $constant)
    {
        return $value >= 0 ? $value >> $numberDecalBit : (($value & 0x7fffffff) >> $numberDecalBit) | $constant;
    }
    /**
     * @param string $source
     * @return int
     */
    public function murmurHash3Int32($source)
    {
        $seed = 0;
        $source = array_values(unpack('C*', $source));
        $keyLength = count($source);
        $h1 = $seed < 0 ? -$seed : $seed;
        $i = 0;
        for ($bytes = $keyLength - ($remainder = $keyLength & 3); $i < $bytes;) {
            $k1 = $source[$i]
                | ($source[++$i] << 8)
                | ($source[++$i] << 16)
                | ($source[++$i] << 24);
            ++$i;
            $k1 = $this->multiply($k1, 0xcc9e2d51);
            $k1 = $this->rotateLeft($k1);
            $k1 = $this->multiply($k1, 0x1b873593);
            $h1 ^= $k1;
            $h1 = $h1 << 13 | ($h1 >= 0 ? $h1 >> 19 : (($h1 & 0x7fffffff) >> 19) | 0x1000);
            $h1b = ((($h1 & 0xffff) * 5) +
                    (((($h1 >= 0 ? $h1 >> 16 :
                                    (($h1 & 0x7fffffff) >> 16) | 0x8000) * 5) & 0xffff) << 16)) & 0xffffffff;
            $h1 = ((($h1b & 0xffff) + 0x6b64) +
                (((($h1b >= 0 ? $h1b >> 16 : (($h1b & 0x7fffffff) >> 16) | 0x8000) + 0xe654) & 0xffff) << 16));
        }
        $k1 = 0;
        switch ($remainder) {
            case 3:
                $k1 ^= $source[$i + 2] << 16;
            case 2:
                $k1 ^= $source[$i + 1] << 8;
            case 1:
                $k1 ^= $source[$i];
                $k1 = $this->multiply($k1, 0xcc9e2d51);
                $k1 = $this->rotateLeft($k1);
                $k1 = $this->multiply($k1, 0x1b873593);
                $h1 ^= $k1;
        }
        $h1 ^= $keyLength;
        $h1 ^= $this->rotateRight($h1, 16, 0x8000);
        $h1 = $this->multiply($h1, 0x85ebca6b);
        $h1 ^= $this->rotateRight($h1, 13, 0x40000);
        $h1 = $this->multiply($h1, 0xc2b2ae35);
        $h1 ^= $this->rotateRight($h1, 16, 0x8000);
        return $h1;
    }
}
