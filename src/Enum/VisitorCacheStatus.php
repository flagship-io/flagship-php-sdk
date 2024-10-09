<?php

namespace Flagship\Enum;

enum VisitorCacheStatus: string
{
    case NONE = 'NONE';
    case ANONYMOUS_ID_CACHE = 'ANONYMOUS_ID_CACHE';
    case VISITOR_ID_CACHE = 'VISITOR_ID_CACHE';
    case VISITOR_ID_CACHE_ONLY = 'VISITOR_ID_CACHE_ONLY';
}
