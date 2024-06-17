<?php

namespace Flagship\Enum;

enum FSFetchReason: int
{
    /**
     * Indicates that there is no specific reason for fetching flags.
     */
    case NONE = 0;

    /**
     * Indicates that the visitor has been created.
     */
    case VISITOR_CREATED = 1;

    /**
     * Indicates that a context has been updated or changed.
     */
    case UPDATE_CONTEXT = 2;

    /**
     * Indicates that the XPC method 'authenticate' has been called.
     */
    case AUTHENTICATE = 3;

    /**
     * Indicates that the XPC method 'unauthenticate' has been called.
     */
    case UNAUTHENTICATE = 4;

    /**
     * Indicates that fetching flags has failed.
     */
    case FETCH_ERROR = 5;

    /**
     * Indicates that flags have been fetched from the cache.
     */
    case READ_FROM_CACHE = 6;
}
