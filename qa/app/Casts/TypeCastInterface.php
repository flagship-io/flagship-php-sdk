<?php

namespace App\Casts;

interface TypeCastInterface
{
    /**
     * @param mixed $defaultValue
     * @param string $type
     * @return float|int|mixed
     */
    public function castToType($defaultValue, $type);
}
