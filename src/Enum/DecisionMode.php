<?php

namespace Flagship\Enum;

/**
 * Class DecisionMode
 *
 * @package Flagship\Enum
 */
class DecisionMode
{
    const DECISION_API = 1;
    const BUCKETING = 2;

    /**
     * return true if a value is valid Decision mode, otherwise false
     *
     * @param  mixed $value value to check
     * @return bool
     */
    public static function isDecisionMode($value)
    {
        switch ($value) {
            case self::BUCKETING:
            case self::DECISION_API:
                return true;
            default:
                return false;
        }
    }

    private static $listDecisionMode = ["DECISION_API", "BUCKETING"];

    /**
     * @param int $decisionMode
     * @return string
     */
    public static function getDecisionModeName($decisionMode)
    {
        if (!is_int($decisionMode) || $decisionMode < 1 || $decisionMode > 2) {
            return "";
        }
        return self::$listDecisionMode[$decisionMode];
    }
}
