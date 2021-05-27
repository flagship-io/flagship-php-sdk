<?php

namespace Flagship\Traits;

use Flagship\Enum\FlagshipConstant;
use Flagship\FlagshipConfig;

trait ValidatorTrait
{
    use LogTrait;

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
        return (is_numeric($value) || is_bool($value) || (is_string($value) && !empty($value)));
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
