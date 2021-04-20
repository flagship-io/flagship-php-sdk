<?php

namespace Abtasty\FlagshipPhpSdk\Enum;

/**
 * Class DecisionMode
 * @package Abtasty\FlagshipPhpSdk\Enum
 */
class DecisionMode
{
    const DECISION_API = 1;

    /**
     * return true if a value is valid Decision mode, otherwise false
     * @param mixed $value value to check
     * @return bool
     */
    public static function isDecisionMode($value)
    {
        switch ($value) {
            case self::DECISION_API:
                return true;
            default:
                return false;
        }
    }
}
