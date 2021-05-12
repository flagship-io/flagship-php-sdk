<?php

namespace App\Casts;

class TypeCast implements TypeCastInterface
{
    /**
     * @inheritDoc
     */
    public function castToType($defaultValue, $type)
    {
        $value = $defaultValue;
        switch ($type) {
            case 'bool':
                $value = filter_var($defaultValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                break;
            case 'double':
            case 'long':
            case 'float':
                $value = (float)$defaultValue;
                break;
            case 'int':
            case 'integer':
                $value = (int)$defaultValue;
                break;
        }
        return $value;
    }
}
