<?php

namespace Flagship\Enum;

/**
 * Class DecisionMode
 *
 * @package Flagship\Enum
 */
enum DecisionMode: int
{
    case DECISION_API = 1;
    case BUCKETING = 2;
}
