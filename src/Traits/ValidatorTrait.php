<?php

namespace Flagship\Traits;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;

trait ValidatorTrait
{
    use LogTrait;

    protected function isJsonObject($string)
    {
        $jsonObject = json_decode($string);

        if ($jsonObject === null) {
            return false;
        }

        $json = ltrim($string);

        if (strpos($json, '{') !== 0) {
            return false;
        }
        return true;
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return bool
     */
    protected function checkType($type, $value)
    {
        switch ($type) {
            case 'bool':
                $check = null !== filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                break;
            case 'double':
            case 'long':
            case 'float':
            case 'int':
            case 'integer':
                $check = is_numeric($value);
                break;
            default:
                $check = is_string($value);
        }
        return $check;
    }

    /**
     * @param $key
     * @param $value
     * @param FlagshipConfig $config
     * @return bool|null
     */
    protected function checkFlagshipContext($key, $value, FlagshipConfig $config)
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
    protected function isKeyValid($key)
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
    protected function isValueValid($value)
    {
        return (is_numeric($value) || is_bool($value) || is_string($value) || is_null($value));
    }

    /**
     * @param $value
     * @param $itemName
     * @param FlagshipConfig $config
     * @return bool
     */
    protected function isNumeric($value, $itemName, FlagshipConfig $config)
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
