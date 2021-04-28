<?php

namespace Flagship\Enum;

/**
 * Global SDK constants
 *
 * @package Flagship\Enum
 */
class FlagshipConstant
{

    /**
     * Default request timeout
     */
    const REQUEST_TIME_OUT = 2000;

    /**
     * SDK language
     */
    const SDK_LANGUAGE = "PHP";

    /**
     * Decision api base url
     */
    const BASE_API_URL = "https://decision.flagship.io/v2";


    const EXPOSE_ALL_KEYS = "exposeAllKeys";

    /**
     * SDK version
     */
    const SDK_VERSION = "v1";

    //Message Error
    const INITIALIZATION_PARAM_ERROR        = "Params 'envId' and 'apiKey' must not be null or empty.";
    const ERROR                          = "error";
    const CONTEXT_PARAM_ERROR           = "params 'key' must be a non null String, and 'value' must be one of the
        following types : String, Number, Boolean, JsonObject, JsonArray.";
    const GET_MODIFICATION_CAST_ERROR    = "Modification for key '%s' has a different type. Default value is returned.";
    const GET_MODIFICATION_MISSING_ERROR = "No modification for key '%s'. Default value is returned.";
    const GET_MODIFICATION_KEY_ERROR     = "Key '%s' must not be null. Default value is returned.";
    const GET_MODIFICATION_INFO_ERROR    = "No modification for key '%s'.";

    //Messages Info
    const SDK_STARTED_INFO = "Flagship SDK (version: %s) READY";

    //Process
    const PROCESS                       = 'process';
    const PROCESS_INITIALIZATION        = 'INITIALIZATION';
    const PROCESS_UPDATE_CONTEXT        = 'UPDATE CONTEXT';
    const PROCESS_GET_MODIFICATION      = 'GET MODIFICATION';
    const PROCESS_GET_MODIFICATION_INFO = 'GET MODIFICATION INFO';
    const NEW_VISITOR                    = 'NEW VISITOR';
}
