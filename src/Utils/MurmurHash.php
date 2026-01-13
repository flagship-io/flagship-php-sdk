<?php

namespace Flagship\Utils;

use InvalidArgumentException;

/**
 * MurmurHash3 32-bit implementation
 * 
 * Provides a fast, non-cryptographic hash function for distributing values
 * uniformly across a hash space. Used for bucketing and consistent hashing.
 * 
 * @see https://en.wikipedia.org/wiki/MurmurHash
 */
class MurmurHash
{
    /**
     * Multiply two 32-bit integers with overflow handling
     * 
     * @param int $k1 First operand
     * @param int $constant Second operand (multiplication constant)
     * @return int Result masked to 32-bit unsigned integer
     */
    private function multiply(int $k1, int $constant): int
    {
        $lowBits = ($k1 & 0xffff) * $constant;
        $highBits = ($this->unsignedRightShift($k1, 16) * $constant & 0xffff) << 16;

        return ($lowBits + $highBits) & 0xffffffff;
    }

    /**
     * Rotate bits left by 15 positions
     * 
     * @param int $k1 Value to rotate
     * @return int Rotated value
     */
    private function rotateLeft(int $k1): int
    {
        return ($k1 << 15) | $this->unsignedRightShift($k1, 17);
    }

    /**
     * Perform unsigned right shift operation
     * 
     * PHP doesn't have native unsigned right shift, so we implement it manually
     * to handle the sign bit correctly.
     * 
     * @param int $value Value to shift
     * @param int $bits Number of bits to shift
     * @return int Unsigned shifted result
     */
    private function unsignedRightShift(int $value, int $bits): int
    {
        if ($value >= 0) {
            return $value >> $bits;
        }

        // Handle negative numbers (sign bit set)
        return (($value & 0x7fffffff) >> $bits) | (0x40000000 >> ($bits - 1));
    }

    /**
     * Rotate bits right with unsigned shift
     * 
     * @param int $value Value to rotate
     * @param int $bits Number of bits to shift
     * @return int Rotated value
     */
    private function rotateRight(int $value, int $bits): int
    {
        return $this->unsignedRightShift($value, $bits);
    }

    /**
     * Convert string to byte array
     * 
     * @param string $source Input string
     * @return array<int> Array of unsigned bytes
     * @throws InvalidArgumentException If unpack fails
     */
    private function stringToBytes(string $source): array
    {
        /**
         * @var array<int>|false $unpacked
         */
        $unpacked = unpack('C*', $source);

        if ($unpacked === false) {
            throw new InvalidArgumentException('Failed to unpack string to bytes');
        }

        return array_values($unpacked);
    }

    /**
     * Compute MurmurHash3 32-bit hash for a string
     * 
     * This implementation follows the MurmurHash3 algorithm specification
     * for generating a 32-bit hash value from an input string.
     * 
     * @param string $source Input string to hash
     * @param int $seed Optional seed value (default: 0)
     * @return int 32-bit hash value
     * @throws InvalidArgumentException If string cannot be processed
     */
    public function murmurHash3Int32(string $source, int $seed = 0): int
    {
        // Convert string to array of unsigned bytes
        $bytes = $this->stringToBytes($source);
        $length = count($bytes);

        // Initialize hash with seed
        $h1 = $seed < 0 ? -$seed : $seed;

        // Process 4-byte chunks
        $chunks = (int)floor($length / 4);
        $remainder = $length % 4;

        for ($i = 0; $i < $chunks * 4; $i += 4) {
            // Combine 4 bytes into 32-bit integer (little-endian)
            $k1 = $bytes[$i]
                | ($bytes[$i + 1] << 8)
                | ($bytes[$i + 2] << 16)
                | ($bytes[$i + 3] << 24);

            // Mix the chunk
            $k1 = $this->mixChunk($k1);

            // Update hash
            $h1 = $this->updateHash($h1, $k1);
        }

        // Process remaining bytes
        if ($remainder > 0) {
            $k1 = $this->processRemainder($bytes, $chunks * 4, $remainder);
            $h1 ^= $k1;
        }

        // Finalize hash
        return $this->finalize($h1, $length);
    }

    /**
     * Mix a 4-byte chunk using MurmurHash3 constants
     * 
     * @param int $k1 Chunk to mix
     * @return int Mixed chunk
     */
    private function mixChunk(int $k1): int
    {
        $k1 = $this->multiply($k1, 0xcc9e2d51);
        $k1 = $this->rotateLeft($k1);
        $k1 = $this->multiply($k1, 0x1b873593);

        return $k1;
    }

    /**
     * Update hash with a mixed chunk
     * 
     * @param int $h1 Current hash value
     * @param int $k1 Mixed chunk
     * @return int Updated hash value
     */
    private function updateHash(int $h1, int $k1): int
    {
        $h1 ^= $k1;
        $h1 = ($h1 << 13) | $this->unsignedRightShift($h1, 19);

        // h1 = h1 * 5 + 0xe6546b64
        $h1b = $this->multiply($h1, 5);
        $h1 = ($h1b + 0xe6546b64) & 0xffffffff;

        return $h1;
    }

    /**
     * Process remaining bytes (less than 4)
     * 
     * @param array<int> $bytes Byte array
     * @param int $offset Starting offset
     * @param int $remainder Number of remaining bytes (1-3)
     * @return int Processed remainder as 32-bit integer
     */
    private function processRemainder(array $bytes, int $offset, int $remainder): int
    {
        $k1 = 0;

        switch ($remainder) {
            case 3:
                $k1 ^= $bytes[$offset + 2] << 16;
                // Fall through
            case 2:
                $k1 ^= $bytes[$offset + 1] << 8;
                // Fall through
            case 1:
                $k1 ^= $bytes[$offset];
                $k1 = $this->mixChunk($k1);
                break;
        }

        return $k1;
    }

    /**
     * Finalize hash with avalanche mixing
     * 
     * @param int $h1 Current hash value
     * @param int $length Original input length
     * @return int Final 32-bit hash
     */
    private function finalize(int $h1, int $length): int
    {
        // XOR with length
        $h1 ^= $length;

        // Final avalanche mixing
        $h1 ^= $this->rotateRight($h1, 16);
        $h1 = $this->multiply($h1, 0x85ebca6b);
        $h1 ^= $this->rotateRight($h1, 13);
        $h1 = $this->multiply($h1, 0xc2b2ae35);
        $h1 ^= $this->rotateRight($h1, 16);

        return $h1;
    }

    /**
     * Compute normalized hash value in range [0, 1)
     * 
     * Useful for percentage-based bucketing and A/B testing.
     * 
     * @param string $source Input string to hash
     * @param int $seed Optional seed value
     * @return float Normalized hash value between 0 and 1
     * @throws InvalidArgumentException If string cannot be processed
     */
    public function murmurHash3Normalized(string $source, int $seed = 0): float
    {
        $hash = $this->murmurHash3Int32($source, $seed);

        // Convert to unsigned 32-bit integer and normalize to [0, 1)
        $unsigned = $hash & 0xffffffff;

        return $unsigned / 0x100000000; // Divide by 2^32
    }
}
