<?php

namespace Flagship\Enum;

class FSFetchReason extends EnumStatusBase
{
    /**
     * Indicates that there is no specific reason for fetching flags.
     */
    const NONE = 0;

    /**
     * Indicates that the visitor has been created.
     */
    const VISITOR_CREATED = 1;
    /**
     * Indicates that a context has been updated or changed.
     */
    const UPDATE_CONTEXT = 2;

    /**
     * Indicates that the XPC method 'authenticate' has been called.
     */
    const AUTHENTICATE = 3;

    /**
     * Indicates that the XPC method 'unauthenticate' has been called.
     */
    const UNAUTHENTICATE = 4;

    /**
     * Indicates that fetching flags has failed.
     */
    const FETCH_ERROR = 5;

    /**
     * Indicates that flags have been fetched from the cache.
     */
    const READ_FROM_CACHE = 6;
}
