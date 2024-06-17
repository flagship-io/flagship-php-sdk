<?php

namespace Flagship\Traits;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;

trait ValidatorTrait
{
    use LogTrait;

    /**
     * @param string $string
     * @return bool
     */
    protected function isJsonObject(string $string): bool
    {
        $jsonObject = json_decode($string);

        if ($jsonObject === null) {
            return false;
        }

        $json = ltrim($string);

        if (!str_starts_with($json, '{')) {
            return false;
        }
        return true;
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return bool
     */
    protected function checkType(string $type, mixed $value): bool
    {
        return match ($type) {
            'bool' => null !== filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'double', 'long', 'float', 'int', 'integer' => is_numeric($value),
            default => is_string($value),
        };
    }

    /**
     * @param $key
     * @param $value
     * @param FlagshipConfig $config
     * @return bool|null
     */
    protected function checkFlagshipContext($key, $value, FlagshipConfig $config): ?bool
    {
        $type = FlagshipContext::getType($key);

        if (!$type) {
            return null;
        }

        $check = $this->checkType($type, $value);

        if (!$check) {
            $this->logError(
                $config,
                sprintf(
                    FlagshipConstant::FLAGSHIP_PREDEFINED_CONTEXT_ERROR,
                    $key,
                    $type
                )
            );
        }
        return $check;
    }

    /**
     * Return true if key is not empty and is a string, otherwise return false
     *
     * @param  mixed $key Context key
     * @return bool
     */
    protected function isKeyValid(mixed $key): bool
    {
        return !empty($key) && is_string($key);
    }

    /**
     * Return true if value is not empty and is a number or a boolean or a string,
     * otherwise return false
     *
     * @param  $value
     * @return bool
     */
    protected function isValueValid($value): bool
    {
        return (is_numeric($value) || is_bool($value) || is_string($value) || is_null($value));
    }

    /**
     * @param $value
     * @param $itemName
     * @param FlagshipConfig $config
     * @return bool
     */
    protected function isNumeric($value, $itemName, FlagshipConfig $config): bool
    {
        if (!is_numeric($value)) {
            $this->logError(
                $config,
                sprintf(FlagshipConstant::TYPE_ERROR, $itemName, 'numeric')
            );
            return false;
        }
        return true;
    }
}
