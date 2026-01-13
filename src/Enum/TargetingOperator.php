<?php

namespace Flagship\Enum;

/**
 * Targeting operator enum for bucketing conditions
 */
enum TargetingOperator: string
{
    case EQUALS = 'EQUALS';
    case NOT_EQUALS = 'NOT_EQUALS';
    case CONTAINS = 'CONTAINS';
    case NOT_CONTAINS = 'NOT_CONTAINS';
    case EXISTS = 'EXISTS';
    case NOT_EXISTS = 'NOT_EXISTS';
    case GREATER_THAN = 'GREATER_THAN';
    case LOWER_THAN = 'LOWER_THAN';
    case GREATER_THAN_OR_EQUALS = 'GREATER_THAN_OR_EQUALS';
    case LOWER_THAN_OR_EQUALS = 'LOWER_THAN_OR_EQUALS';
    case STARTS_WITH = 'STARTS_WITH';
    case ENDS_WITH = 'ENDS_WITH';
}
