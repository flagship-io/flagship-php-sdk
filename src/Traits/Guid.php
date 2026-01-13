<?php

namespace Flagship\Traits;

trait Guid
{
    /**
     * @return string
     */
    protected function newGuid(): string
    {
        $rand = function (int $min, int $max): int {
            return rand($min, $max);
        };

        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            $rand(0, 0xffff),
            $rand(0, 0xffff),
            $rand(0, 0xffff),
            $rand(0, 0x0fff) | 0x4000,
            $rand(0, 0x3fff) | 0x8000,
            $rand(0, 0xffff),
            $rand(0, 0xffff),
            $rand(0, 0xffff)
        );
    }
}
